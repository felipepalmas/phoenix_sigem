<?php

/**
 * Componente da aplicação Phoenix - SIGEM (Zero Papel)
 * Controller do Módulo de Protocolo e Motor de Tramitação Digital
 *
 * @license AGPL-3.0
 */

namespace App\Http\Controllers;

use App\Models\AuditProcessoSigiloso;
use App\Models\Cliente;
use App\Models\Processo;
use App\Models\ProcessoDocumento;
use App\Models\Tramitacao;
use App\Models\UnidadeAdministrativa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProtocoloController extends Controller
{
    /**
     * Verifica se o usuário autenticado pode acessar um processo restrito ou sigiloso.
     */
    protected function podeAcessarSigiloso(Processo $processo): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        if ($user->ehGestorGeral() || $user->hasPermission('protocolo.sigiloso')) {
            return true;
        }

        if ($processo->created_by === $user->id) {
            return true;
        }

        if ($user->unidade_administrativa_id && $processo->unidade_atual_id === $user->unidade_administrativa_id) {
            return true;
        }

        return false;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $canViewAllSigilosos = $user && ($user->ehGestorGeral() || $user->hasPermission('protocolo.sigiloso'));

        $query = Processo::with(['interessado', 'unidadeAtual', 'usuarioResponsavel'])
            ->latest('id');

        // Restrição de visualização conforme LAI / Sigilo
        if (! $canViewAllSigilosos) {
            $query->where(function ($q) use ($user) {
                $q->where('sigilo', 'publico');
                if ($user) {
                    $q->orWhere('created_by', $user->id);
                    if ($user->unidade_administrativa_id) {
                        $q->orWhere('unidade_atual_id', $user->unidade_administrativa_id);
                    }
                }
            });
        }

        // Recorte rápido (abas)
        $unidadeId = $user?->unidade_administrativa_id;
        $recortes = [
            'secretaria' => fn ($q) => $q->where('unidade_atual_id', $unidadeId ?: 0)->whereIn('status', ['tramitando', 'suspenso']),
            'comigo' => fn ($q) => $q->where('usuario_responsavel_id', $user?->id ?: 0)->whereIn('status', ['tramitando', 'suspenso']),
            'atrasados' => fn ($q) => $q->whereIn('status', ['tramitando', 'suspenso'])->whereNotNull('data_limite')->whereDate('data_limite', '<', now()->toDateString()),
            'andamento' => fn ($q) => $q->whereIn('status', ['tramitando', 'suspenso']),
            'todos' => fn ($q) => $q,
        ];
        $padrao = $unidadeId ? 'secretaria' : 'andamento';
        $recorte = array_key_exists($request->input('recorte'), $recortes) ? $request->input('recorte') : $padrao;
        $baseVisivel = clone $query;
        $contagens = [];
        foreach ($recortes as $chave => $fn) {
            $contagens[$chave] = $fn(clone $baseVisivel)->count();
        }
        $recortes[$recorte]($query);

        if ($request->filled('busca')) {
            $busca = $request->input('busca');
            $query->where(function ($q) use ($busca) {
                $q->where('numero', 'like', "%{$busca}%")
                  ->orWhere('assunto', 'like', "%{$busca}%")
                  ->orWhere('descricao', 'like', "%{$busca}%")
                  ->orWhereHas('interessado', function ($cli) use ($busca) {
                      $cli->where('nome', 'like', "%{$busca}%")
                          ->orWhere('razao_social', 'like', "%{$busca}%")
                          ->orWhere('cpf', 'like', "%{$busca}%")
                          ->orWhere('cnpj', 'like', "%{$busca}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('prioridade')) {
            $query->where('prioridade', $request->input('prioridade'));
        }

        if ($request->filled('unidade_id')) {
            $query->where('unidade_atual_id', $request->input('unidade_id'));
        }

        $processos = $query->with(['tramitacoes' => fn ($t) => $t->where('status', 'enviado')])
            ->paginate(20)->withQueryString();
        $unidades = UnidadeAdministrativa::where('ativo', true)->orderBy('sigla')->orderBy('nome')->get();

        $abas = [];
        $rotulos = $unidadeId
            ? ['secretaria' => 'Na minha secretaria', 'comigo' => 'Comigo', 'atrasados' => 'Atrasados', 'andamento' => 'Em andamento', 'todos' => 'Todos']
            : ['andamento' => 'Em andamento', 'comigo' => 'Comigo', 'atrasados' => 'Atrasados', 'todos' => 'Todos'];
        foreach ($rotulos as $chave => $rotulo) {
            $abas[] = [
                'name' => $rotulo,
                'path' => route('protocolo.index', ['recorte' => $chave]),
                'active' => $recorte === $chave,
                'count' => $contagens[$chave],
            ];
        }

        return erp_view('protocolo.index', [
            'title' => 'Protocolo',
            'processos' => $processos,
            'unidades' => $unidades,
            'abas' => $abas,
            'recorte' => $recorte,
            'filtrosAtivos' => $request->hasAny(['busca', 'status', 'prioridade', 'unidade_id']) && collect($request->only(['busca', 'status', 'prioridade', 'unidade_id']))->filter()->isNotEmpty(),
        ]);
    }

    public function create()
    {
        $unidades = UnidadeAdministrativa::where('ativo', true)->orderBy('ordem')->orderBy('nome')->get();
        $cidadaos = Cliente::whereNull('deleted_at')->orderBy('nome')->limit(1000)->get(['id', 'nome', 'razao_social', 'cpf', 'cnpj', 'tipo_pessoa']);

        return erp_view('protocolo.create', [
            'title' => 'Autuar processo',
            'unidades' => $unidades,
            'cidadaos' => $cidadaos,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'interessado_id' => 'required|exists:clientes,id',
            'assunto' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'unidade_atual_id' => 'required|exists:unidades_administrativas,id',
            'prioridade' => 'required|in:baixa,normal,urgente',
            'sigilo' => 'required|in:publico,restrito,sigiloso',
            'prazo_dias' => 'nullable|integer|min:1|max:365',
            'documento_inicial' => 'nullable|file|mimes:pdf,png,jpg,jpeg,webp,odt,docx,doc,txt,xls,xlsx,csv|max:20480', // até 20MB
            'titulo_documento' => 'nullable|string|max:255',
        ]);

        $processo = DB::transaction(function () use ($validated, $request) {
            $dadosNumero = Processo::gerarProximoNumero();

            $dataLimite = null;
            if (!empty($validated['prazo_dias'])) {
                $dataLimite = now()->addDays((int) $validated['prazo_dias'])->toDateString();
            }

            $proc = Processo::create([
                'numero' => $dadosNumero['numero'],
                'ano' => $dadosNumero['ano'],
                'sequencial' => $dadosNumero['sequencial'],
                'interessado_id' => $validated['interessado_id'],
                'assunto' => $validated['assunto'],
                'descricao' => $validated['descricao'] ?? null,
                'unidade_atual_id' => $validated['unidade_atual_id'],
                'status' => 'tramitando',
                'prioridade' => $validated['prioridade'],
                'sigilo' => $validated['sigilo'],
                'prazo_dias' => $validated['prazo_dias'] ?? null,
                'data_limite' => $dataLimite,
                'created_by' => auth()->id(),
            ]);

            // Registrar a tramitação inaugural (Abertura / Autuação)
            Tramitacao::create([
                'processo_id' => $proc->id,
                'unidade_origem_id' => $validated['unidade_atual_id'],
                'unidade_destino_id' => $validated['unidade_atual_id'],
                'usuario_envio_id' => auth()->id(),
                'despacho' => 'Processo autuado no sistema e encaminhado à unidade responsável.',
                'parecer_tipo' => 'informativo',
                'status' => 'enviado',
                'data_envio' => now(),
                'created_by' => auth()->id(),
            ]);

            // Se houver anexo inicial
            if ($request->hasFile('documento_inicial') && $request->file('documento_inicial')->isValid()) {
                $file = $request->file('documento_inicial');
                $path = $file->store('processos/' . $proc->ano . '/' . $proc->id, 'local');
                $fullPath = Storage::disk('local')->path($path);
                $hash = file_exists($fullPath) ? hash_file('sha256', $fullPath) : null;

                ProcessoDocumento::create([
                    'processo_id' => $proc->id,
                    'titulo' => $validated['titulo_documento'] ?: 'Requerimento / Peça Inicial',
                    'tipo_documento' => 'requerimento',
                    'caminho_arquivo' => $path,
                    'nome_original' => $file->getClientOriginalName(),
                    'tamanho_bytes' => $file->getSize(),
                    'mime_type' => $file->getClientMimeType(),
                    'hash_sha256' => $hash,
                    'created_by' => auth()->id(),
                ]);
            }

            return $proc;
        });

        return redirect()->route('protocolo.show', $processo)
            ->with('success', "Processo nº {$processo->numero} autuado com sucesso!");
    }

    public function show(Processo $processo)
    {
        $user = auth()->user();

        // Bloqueio rigoroso de sigilo LAI/LGPD
        if (in_array($processo->sigilo, ['sigiloso', 'restrito'])) {
            if (! $this->podeAcessarSigiloso($processo)) {
                AuditProcessoSigiloso::create([
                    'processo_id' => $processo->id,
                    'user_id' => $user?->id ?? null,
                    'unidade_id' => $user?->unidade_administrativa_id ?? null,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'acao' => 'bloqueio_acesso_negado',
                ]);

                abort(403, 'Acesso Restrito: Este processo administrativo está classificado como ' . strtoupper($processo->sigilo) . ' nos termos da Lei Federal nº 12.527/2011 (LAI). Seu usuário não possui lotação nem permissão para visualizá-lo.');
            }

            // Trilha de Auditoria para visualizações legítimas de processos sigilosos
            AuditProcessoSigiloso::create([
                'processo_id' => $processo->id,
                'user_id' => $user->id,
                'unidade_id' => $user->unidade_administrativa_id ?? null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'acao' => 'visualizacao',
            ]);
        }

        $processo->load([
            'interessado',
            'unidadeAtual',
            'usuarioResponsavel',
            'criador',
            'documentos.assinadoPor',
            'documentos.criador',
            'tramitacoes.unidadeOrigem',
            'tramitacoes.unidadeDestino',
            'tramitacoes.usuarioEnvio',
            'tramitacoes.usuarioRecebimento',
        ]);

        $unidades = UnidadeAdministrativa::where('ativo', true)
            ->where('id', '!=', $processo->unidade_atual_id)
            ->orderBy('ordem')
            ->orderBy('nome')
            ->get();

        $user = auth()->user();
        $lotadoAqui = $user->lotadoEm($processo->unidade_atual_id) || $user->is_admin || $user->hasRole('admin');

        return erp_view('protocolo.show', [
            'title' => "Processo {$processo->numero}",
            'processo' => $processo,
            'unidades' => $unidades,
            'aguardaRecebimento' => $processo->aguardaRecebimento(),
            'pendente' => $processo->tramitacaoPendente(),
            'podeAgir' => $lotadoAqui,
            'podeAnexar' => ! $processo->estaEncerrado() && ($user->ehGestorGeral() || $user->lotadoEm($processo->unidade_atual_id) || (int) $processo->created_by === (int) $user->id),
        ]);
    }

    /**
     * Tramitar para nova unidade com despacho
     */
    public function tramitar(Request $request, Processo $processo)
    {
        $user = auth()->user();

        if (! $processo->podeSerTramitado()) {
            return redirect()->route('protocolo.show', $processo)
                ->with('error', "Este processo não pode ser tramitado pois seu status atual é '{$processo->status}'.");
        }

        $canTramitar = (bool) $user->is_admin || $user->hasRole('admin') ||
            ($user->unidade_administrativa_id && $user->unidade_administrativa_id === $processo->unidade_atual_id);

        if (! $canTramitar) {
            return redirect()->route('protocolo.show', $processo)
                ->with('error', 'Acesso negado: Apenas servidores lotados na unidade atual do processo ou administradores podem despachar ou tramitá-lo.');
        }

        $validated = $request->validate([
            'unidade_destino_id' => 'required|integer|exists:unidades_administrativas,id|not_in:' . (int) $processo->unidade_atual_id,
            'despacho' => 'required|string|min:5',
            'parecer_tipo' => 'required|in:favoravel,desfavoravel,informativo,diligencia',
        ]);

        $processo->tramitarPara(
            (int) $validated['unidade_destino_id'],
            $validated['despacho'],
            $user->id,
            $validated['parecer_tipo']
        );

        return redirect()->route('protocolo.show', $processo)
            ->with('success', 'Processo tramitado com sucesso para a nova unidade!');
    }

    /**
     * Receber o processo na unidade atual
     */
    public function receber(Processo $processo)
    {
        $user = auth()->user();

        if (! $processo->aguardaRecebimento()) {
            return redirect()->route('protocolo.show', $processo)
                ->with('error', 'Não há envio pendente de recebimento para este processo.');
        }

        $canReceber = (bool) $user->is_admin || $user->hasRole('admin') ||
            ($user->unidade_administrativa_id && $user->unidade_administrativa_id === $processo->unidade_atual_id);

        if (! $canReceber) {
            return redirect()->route('protocolo.show', $processo)
                ->with('error', 'Acesso negado: Apenas servidores lotados na unidade de destino ou administradores podem receber o processo.');
        }

        $processo->receberNaUnidade($user->id);

        return redirect()->route('protocolo.show', $processo)
            ->with('success', 'Processo recebido com sucesso na unidade!');
    }

    /**
     * Concluir / Deferir / Indeferir / Arquivar o processo
     */
    public function concluir(Request $request, Processo $processo)
    {
        $user = auth()->user();

        if (! $processo->podeSerConcluido()) {
            return redirect()->route('protocolo.show', $processo)
                ->with('error', "Este processo já se encontra concluído ou arquivado ('{$processo->status}').");
        }

        $canConcluir = (bool) $user->is_admin || $user->hasRole('admin') ||
            ($user->unidade_administrativa_id && $user->unidade_administrativa_id === $processo->unidade_atual_id);

        if (! $canConcluir) {
            return redirect()->route('protocolo.show', $processo)
                ->with('error', 'Apenas servidores lotados na unidade atual do processo ou administradores podem proferir decisão de conclusão.');
        }

        $validated = $request->validate([
            'status' => 'required|in:deferido,indeferido,arquivado,suspenso',
            'despacho_final' => 'required|string|min:5',
        ]);

        $processo->concluir(
            $validated['status'],
            $validated['despacho_final'],
            $user->id
        );

        return redirect()->route('protocolo.show', $processo)
            ->with('success', 'Decisão / Conclusão registrada no processo com sucesso!');
    }

    /**
     * Adicionar novo anexo / documento ao processo com hash SHA-256
     */
    public function anexarDocumento(Request $request, Processo $processo)
    {
        $user = auth()->user();
        if ($processo->estaEncerrado()) {
            return redirect()->route('protocolo.show', $processo)
                ->with('error', 'Processo encerrado não recebe novos documentos.');
        }
        $podeAnexar = $user->ehGestorGeral()
            || $user->lotadoEm($processo->unidade_atual_id)
            || (int) $processo->created_by === (int) $user->id;
        if (! $podeAnexar) {
            return redirect()->route('protocolo.show', $processo)
                ->with('error', 'Apenas servidores lotados na unidade atual do processo podem anexar documentos.');
        }

        $validated = $request->validate([
            'arquivo' => 'required|file|mimes:pdf,png,jpg,jpeg,webp,odt,docx,doc,txt,xls,xlsx,csv|max:20480',
            'titulo' => 'required|string|max:255',
            'tipo_documento' => 'required|in:parecer,despacho,oficio,certidao,anexo',
        ]);

        $file = $request->file('arquivo');
        $path = $file->store('processos/' . $processo->ano . '/' . $processo->id, 'local');
        $fullPath = Storage::disk('local')->path($path);
        $hash = file_exists($fullPath) ? hash_file('sha256', $fullPath) : null;

        ProcessoDocumento::create([
            'processo_id' => $processo->id,
            'titulo' => $validated['titulo'],
            'tipo_documento' => $validated['tipo_documento'],
            'caminho_arquivo' => $path,
            'nome_original' => $file->getClientOriginalName(),
            'tamanho_bytes' => $file->getSize(),
            'mime_type' => $file->getClientMimeType(),
            'hash_sha256' => $hash,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('protocolo.show', $processo)
            ->with('success', 'Documento anexado ao processo com sucesso!');
    }

    /**
     * Download seguro de documento do processo
     */
    public function downloadDocumento(Processo $processo, ProcessoDocumento $documento)
    {
        if ($documento->processo_id !== $processo->id) {
            abort(404);
        }

        // Verifica autorização de sigilo
        if (in_array($processo->sigilo, ['sigiloso', 'restrito']) && ! $this->podeAcessarSigiloso($processo)) {
            abort(403, 'Acesso restrito ao documento conforme classificação do processo.');
        }

        if (!Storage::disk('local')->exists($documento->caminho_arquivo)) {
            return back()->with('error', 'Arquivo não encontrado no armazenamento.');
        }

        return Storage::disk('local')->download(
            $documento->caminho_arquivo,
            $documento->nome_original
        );
    }
}
