<?php

/**
 * Componente da aplicação Phoenix - SIGEM
 * Módulo B: Zeladoria Urbana e Serviços Públicos Municipais
 *
 * @license AGPL-3.0
 */

namespace App\Http\Controllers;

use App\Models\CategoriaZeladoria;
use App\Models\Cliente;
use App\Models\OrdemServico;
use App\Models\Processo;
use App\Models\StatusOs;
use App\Models\UnidadeAdministrativa;
use App\Support\StatusZeladoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ZeladoriaController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $unidadeId = $user->unidade_administrativa_id;

        $base = OrdemServico::whereNotNull('categoria_zeladoria_id');

        $recortes = [
            'secretaria' => fn ($q) => $q->where('secretaria_responsavel_id', $unidadeId ?: 0)->whereNotIn('status', StatusZeladoria::ENCERRADAS),
            'triagem' => fn ($q) => $q->whereIn('status', StatusZeladoria::ABERTAS),
            'campo' => fn ($q) => $q->whereIn('status', StatusZeladoria::EM_CAMPO),
            'concluidas' => fn ($q) => $q->whereIn('status', StatusZeladoria::ENCERRADAS),
            'todas' => fn ($q) => $q,
        ];
        $rotulos = $unidadeId
            ? ['secretaria' => 'Da minha secretaria', 'triagem' => 'Em triagem', 'campo' => 'Em campo', 'concluidas' => 'Encerradas', 'todas' => 'Todas']
            : ['triagem' => 'Em triagem', 'campo' => 'Em campo', 'concluidas' => 'Encerradas', 'todas' => 'Todas'];
        $padrao = $unidadeId ? 'secretaria' : 'triagem';
        $recorte = array_key_exists($request->input('recorte'), $rotulos) ? $request->input('recorte') : $padrao;

        $abas = [];
        foreach ($rotulos as $chave => $rotulo) {
            $abas[] = [
                'name' => $rotulo,
                'path' => route('zeladoria.index', ['recorte' => $chave]),
                'active' => $chave === $recorte,
                'count' => $recortes[$chave](clone $base)->count(),
            ];
        }

        $query = $recortes[$recorte]((clone $base)->with(['cliente', 'categoriaZeladoria', 'secretariaResponsavel']));

        if ($request->filled('busca')) {
            $busca = $request->input('busca');
            $query->where(function ($q) use ($busca) {
                $q->where('codigo_interno', 'like', "%{$busca}%")
                  ->orWhere('logradouro', 'like', "%{$busca}%")
                  ->orWhere('bairro', 'like', "%{$busca}%")
                  ->orWhere('relato_cliente', 'like', "%{$busca}%")
                  ->orWhereHas('cliente', fn ($cli) => $cli->where('nome', 'like', "%{$busca}%"));
            });
        }
        if ($request->filled('categoria_id')) {
            $query->where('categoria_zeladoria_id', $request->input('categoria_id'));
        }
        if ($request->filled('bairro')) {
            $query->where('bairro', 'like', '%' . $request->input('bairro') . '%');
        }

        $pontosMapa = (clone $query)->whereNotNull('latitude')->whereNotNull('longitude')->where('latitude', '!=', 0)
            ->limit(200)->get()
            ->map(fn ($os) => [
                'codigo' => $os->codigo_interno ?: ('#' . $os->id),
                'categoria' => $os->categoriaZeladoria->nome ?? 'Zeladoria',
                'local' => trim(($os->logradouro ?? '') . ($os->bairro ? ' — ' . $os->bairro : '')),
                'situacao' => StatusZeladoria::rotulo($os->status),
                'lat' => (float) $os->latitude,
                'lng' => (float) $os->longitude,
                'url' => route('zeladoria.show', $os),
            ])->values();

        $ordens = $query->orderByRaw("CASE WHEN prioridade IN ('urgente','emergencial') THEN 0 ELSE 1 END")
            ->latest('id')->paginate(20)->withQueryString();

        return erp_view('zeladoria.index', [
            'title' => 'Zeladoria',
            'ordens' => $ordens,
            'categorias' => CategoriaZeladoria::where('ativo', true)->orderBy('ordem')->get(),
            'abas' => $abas,
            'recorte' => $recorte,
            'pontosMapa' => $pontosMapa,
            'filtrosAtivos' => collect($request->only(['busca', 'categoria_id', 'bairro']))->filter()->isNotEmpty(),
        ]);
    }

    public function create()
    {
        $categorias = CategoriaZeladoria::where('ativo', true)->orderBy('ordem')->get();
        $secretarias = UnidadeAdministrativa::where('ativo', true)->orderBy('ordem')->get();
        $cidadaos = Cliente::where('ativo', true)->orderBy('nome')->limit(100)->get();
        $processos = Processo::visiveisPara(auth()->user())->whereIn('status', ['tramitando', 'suspenso'])->latest('id')->limit(50)->get();

        return erp_view('zeladoria.create', [
            'title' => 'Nova demanda',
            'categorias' => $categorias,
            'secretarias' => $secretarias,
            'cidadaos' => $cidadaos,
            'processos' => $processos,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'categoria_zeladoria_id' => 'required|exists:categorias_zeladoria,id',
            'secretaria_responsavel_id' => 'nullable|exists:unidades_administrativas,id',
            'processo_id' => 'nullable|exists:processos,id',
            'prioridade' => 'required|in:baixa,normal,urgente,emergencial',
            'origem_demanda' => 'required|string|max:50',
            'logradouro' => 'required|string|max:255',
            'numero_logradouro' => 'nullable|string|max:30',
            'bairro' => 'required|string|max:100',
            'referencia_local' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'relato_cliente' => 'required|string|min:5',
            'foto_antes' => 'nullable|image|max:15360', // até 15MB
        ]);

        $categoria = CategoriaZeladoria::find($validated['categoria_zeladoria_id']);

        // Se secretaria não informada, herda da categoria
        $secretariaId = $validated['secretaria_responsavel_id'] ?? ($categoria ? $categoria->secretaria_padrao_id : null);

        // Upload de foto antes (comprovação inicial)
        $caminhoFotoAntes = null;
        if ($request->hasFile('foto_antes') && $request->file('foto_antes')->isValid()) {
            $caminhoFotoAntes = $request->file('foto_antes')->store('zeladoria/' . date('Y/m'), 'public');
        }

        // Gerar código sequencial interno (ex: ZEL-2026/00001)
        $ano = (int) date('Y');
        $codigoInterno = OrdemServico::gerarCodigoZeladoria($ano);

        $prazoDias = $categoria ? $categoria->prazo_padrao_dias : 7;
        $previstaConclusao = now()->addDays($prazoDias);

        $os = OrdemServico::create([
            'codigo_interno' => $codigoInterno,
            'chave_acesso' => Processo::novaChaveAcesso(),
            'cliente_id' => $validated['cliente_id'],
            'categoria_zeladoria_id' => $validated['categoria_zeladoria_id'],
            'secretaria_responsavel_id' => $secretariaId,
            'processo_id' => $validated['processo_id'] ?? null,
            'prioridade' => $validated['prioridade'],
            'origem_demanda' => $validated['origem_demanda'],
            'status' => StatusZeladoria::REGISTRADA,
            'tipo_os' => 'Zeladoria Urbana',
            'data_abertura' => now(),
            'prevista_inicio' => now(),
            'prevista_conclusao' => $previstaConclusao,
            'logradouro' => $validated['logradouro'],
            'numero_logradouro' => $validated['numero_logradouro'] ?? null,
            'bairro' => $validated['bairro'],
            'referencia_local' => $validated['referencia_local'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'foto_antes' => $caminhoFotoAntes,
            'relato_cliente' => $validated['relato_cliente'],
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('zeladoria.show', $os)
            ->with('success', "Demanda de Zeladoria nº {$os->codigo_interno} registrada com sucesso!");
    }

    public function show(OrdemServico $ordemServico)
    {
        $ordemServico->load([
            'cliente',
            'categoriaZeladoria',
            'secretariaResponsavel',
            'processo',
            'servicos',
            'produtos',
            'tecnicos.usuario',
        ]);

        return erp_view('zeladoria.show', [
            'title' => 'Demanda ' . ($ordemServico->codigo_interno ?: '#' . $ordemServico->id),
            'os' => $ordemServico,
            'encerrada' => StatusZeladoria::encerrada($ordemServico->status),
            'podeOperar' => $this->podeOperar($ordemServico),
        ]);
    }

    /**
     * Quem pode mexer na demanda: gestor geral, ou servidor lotado na secretaria responsável
     * com a permissão zeladoria.concluir. Demanda sem secretaria: gestor geral ou quem tem a permissão.
     */
    protected function podeOperar(OrdemServico $os): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }
        if ($os->secretaria_responsavel_id === null) {
            return $user->ehGestorGeral() || $user->hasPermission('zeladoria.concluir');
        }

        return $user->podeNaUnidade('zeladoria.concluir', $os->secretaria_responsavel_id);
    }

    /**
     * Mudança de situação (programar, iniciar execução, aguardar material, cancelar).
     */
    public function andamento(Request $request, OrdemServico $ordemServico)
    {
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', StatusZeladoria::ANDAMENTO),
            'observacao' => 'nullable|string|max:1000',
        ]);

        if (StatusZeladoria::encerrada($ordemServico->status)) {
            return back()->with('error', 'Esta demanda já está encerrada (' . StatusZeladoria::rotulo($ordemServico->status) . ').');
        }
        if (! $this->podeOperar($ordemServico)) {
            return back()->with('error', 'Somente servidores da secretaria responsável podem alterar a situação desta demanda.');
        }

        $dados = ['status' => $validated['status'], 'updated_by' => auth()->id()];
        if ($validated['status'] === StatusZeladoria::EM_EXECUCAO && empty($ordemServico->real_inicio)) {
            $dados['real_inicio'] = now();
        }
        if (! empty($validated['observacao'])) {
            $linha = now()->format('d/m/Y H:i') . ' — ' . auth()->user()->name . ' (' . $validated['status'] . '): ' . $validated['observacao'];
            $dados['observacoes_internas'] = trim(($ordemServico->observacoes_internas ? $ordemServico->observacoes_internas . "\n" : '') . $linha);
        }
        $ordemServico->update($dados);

        return redirect()->route('zeladoria.show', $ordemServico)
            ->with('success', 'Situação atualizada para "' . $validated['status'] . '".');
    }

    /**
     * Conclusão da demanda com foto "depois" e atesto do servidor autenticado.
     */
    public function concluir(Request $request, OrdemServico $ordemServico)
    {
        $validated = $request->validate([
            'foto_depois' => 'required|image|max:15360',
            'servicos_realizados' => 'required|string|min:5',
            'atesto_cargo' => 'nullable|string|max:100',
        ]);

        if (StatusZeladoria::encerrada($ordemServico->status)) {
            return back()->with('error', 'Esta demanda já está encerrada (' . StatusZeladoria::rotulo($ordemServico->status) . ') e não pode ser concluída novamente.');
        }
        if (! $this->podeOperar($ordemServico)) {
            return back()->with('error', 'Somente servidores da secretaria responsável podem atestar a conclusão desta demanda.');
        }

        $user = auth()->user();
        $caminhoFotoDepois = $request->file('foto_depois')->store('zeladoria/' . date('Y/m'), 'public');

        $ordemServico->update([
            'status' => StatusZeladoria::CONCLUIDA,
            'real_conclusao' => now(),
            'servicos_realizados' => $validated['servicos_realizados'],
            'foto_depois' => $caminhoFotoDepois,
            // O atesto é do usuário autenticado (não um nome digitado)
            'atesto_responsavel' => $user->name,
            'atesto_cargo' => ($validated['atesto_cargo'] ?? null) ?: ($user->cargo ?: 'Servidor(a) municipal'),
            'atesto_em' => now(),
            'updated_by' => $user->id,
        ]);

        return redirect()->route('zeladoria.show', $ordemServico)
            ->with('success', 'Demanda concluída com foto e atesto registrados.');
    }
}
