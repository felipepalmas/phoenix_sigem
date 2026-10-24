<?php

/**
 * Componente da aplicação Phoenix - SIGEM
 * Portal do Cidadão & Transparência Pública Municipal (Lei 14.129/2021)
 *
 * @license AGPL-3.0
 */

namespace App\Http\Controllers;

use App\Models\CategoriaZeladoria;
use App\Models\Cliente;
use App\Models\Contato;
use App\Models\OrdemServico;
use App\Models\Processo;
use App\Models\ProcessoDocumento;
use App\Models\UnidadeAdministrativa;
use App\Models\Veiculo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PortalCidadaoController extends Controller
{
    /**
     * Mascara o nome do cidadão para conformidade com a LGPD (Lei 13.709/2018).
     * Exemplo: "Maria da Silva Santos" -> "M**** d* S**** S****"
     */
    public static function mascararNome(?string $nome): string
    {
        if (empty($nome)) {
            return 'Cidadão Municipal';
        }

        $partes = explode(' ', trim($nome));
        $mascaradas = [];

        foreach ($partes as $parte) {
            $len = mb_strlen($parte);
            if ($len <= 2) {
                $mascaradas[] = mb_substr($parte, 0, 1) . '*';
            } else {
                $mascaradas[] = mb_substr($parte, 0, 1) . str_repeat('*', min(4, $len - 1));
            }
        }

        return implode(' ', $mascaradas);
    }

    /**
     * Mascara o CPF do cidadão para conformidade com a LGPD.
     * Exemplo: "123.456.789-00" ou "12345678900" -> "***.456.789-**"
     */
    public static function mascararCpf(?string $cpf): string
    {
        if (empty($cpf)) {
            return '***.***.***-**';
        }

        $digitos = preg_replace('/\D/', '', $cpf);
        if (strlen($digitos) === 11) {
            return '***.' . substr($digitos, 3, 3) . '.' . substr($digitos, 6, 3) . '-**';
        }

        return '***.***.***-**';
    }

    /**
     * Valida os dígitos verificadores do CPF (algoritmo oficial Receita Federal).
     */
    public static function validarCpf(?string $cpf): bool
    {
        if (empty($cpf)) {
            return false;
        }

        $cpf = preg_replace('/\D/', '', $cpf);
        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $d = 0;
            for ($c = 0; $c < $t; $c++) {
                $d += (int) $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ((int) $cpf[$t] !== $d) {
                return false;
            }
        }

        return true;
    }

    /**
     * Página Inicial do Portal do Cidadão & Transparência Pública.
     */
    public function index()
    {
        // Estatísticas públicas da prefeitura para transparência
        $stats = [
            'processos_total' => Processo::count(),
            'processos_concluidos' => Processo::whereIn('status', ['deferido', 'indeferido', 'arquivado', 'concluido'])->count(),
            'zeladoria_atendida' => OrdemServico::whereNotNull('categoria_zeladoria_id')->whereIn('status', \App\Support\StatusZeladoria::CONCLUIDAS)->count(),
            'zeladoria_ativa' => OrdemServico::whereNotNull('categoria_zeladoria_id')->whereNotIn('status', \App\Support\StatusZeladoria::ENCERRADAS)->count(),
            'veiculos_operacao' => Veiculo::where('ativo', true)->count(),
        ];

        // Categorias de zeladoria populares para exibição no portal
        $categoriasZeladoria = CategoriaZeladoria::where('ativo', true)
            ->orderBy('ordem')
            ->take(6)
            ->get();

        // Secretarias municipais para guia de atendimento ao munícipe
        $secretarias = UnidadeAdministrativa::with('responsavel')->where('ativo', true)
            ->orderBy('sigla')
            ->take(8)
            ->get();

        return view('portal.index', compact('stats', 'categoriasZeladoria', 'secretarias'));
    }

    /**
     * Consulta pública de Processos / Protocolos Administrativos sem necessidade de login.
     */
    public function consultarProcesso(Request $request)
    {
        $numero = trim((string) $request->input('numero', $request->input('q', '')));

        if (empty($numero)) {
            return redirect()->route('portal.index')
                ->with('warning', 'Por favor, informe o número completo do processo ou protocolo para consultar.');
        }

        // Se o cidadão digitou um código de zeladoria (ex: ZEL-2026/00001 ou OS-...), redireciona para zeladoria
        if (Str::startsWith(strtoupper($numero), ['ZEL', 'OS-'])) {
            return redirect()->route('portal.zeladoria.show', array_filter(['codigo' => strtoupper($numero), 'chave' => $request->input('chave')]));
        }

        // Busca o processo estritamente por número oficial exato (prevenção de enumeração)
        $processo = Processo::with([
            'interessado',
            'unidadeAtual',
            'tramitacoes.unidadeOrigem',
            'tramitacoes.unidadeDestino',
            'tramitacoes.usuarioEnvio',
            'documentos'
        ])
        ->where('numero', $numero)
        ->first();

        // Número + chave do comprovante (impede varrer 2026/000001, 000002...)
        $chave = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $request->input('chave', '')));
        if ($processo && $processo->chave_acesso && ! hash_equals((string) $processo->chave_acesso, $chave)) {
            $processo = null;
        }

        $termoBuscado = $numero;

        if (! $processo) {
            return view('portal.processo-resultado', [
                'processo' => null,
                'termoBuscado' => $termoBuscado,
                'erro' => "Não encontramos um processo com o número \"{$numero}\" e a chave de acesso informada. Confira os dois dados no seu comprovante de protocolo.",
            ]);
        }

        // Trata proteção contra sigilo legal (Lei 12.527/2011 - LAI)
        $isSigiloso = in_array($processo->sigilo, ['sigiloso', 'restrito']);

        // Aplica mascaramento LGPD aos dados do requerente
        $dadosRequerente = [
            'nome' => self::mascararNome($processo->interessado?->nome),
            'cpf' => self::mascararCpf($processo->interessado?->cpf ?? $processo->interessado?->cnpj),
        ];

        return view('portal.processo-resultado', compact('processo', 'termoBuscado', 'isSigiloso', 'dadosRequerente'));
    }

    /**
     * Formulário de autoatendimento para o cidadão abrir demanda urbana de zeladoria.
     */
    public function novoChamadoZeladoria()
    {
        $categorias = CategoriaZeladoria::where('ativo', true)
            ->orderBy('ordem')
            ->get();

        return view('portal.zeladoria-nova', compact('categorias'));
    }

    /**
     * Gravação da solicitação pública de zeladoria aberta pelo cidadão.
     */
    public function salvarChamadoZeladoria(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:120',
            'cpf' => 'required|string|max:20',
            'telefone' => 'required|string|max:25',
            'email' => 'nullable|email|max:100',
            'categoria_zeladoria_id' => 'required|exists:categorias_zeladoria,id',
            'logradouro' => 'required|string|max:200',
            'numero' => 'nullable|string|max:20',
            'bairro' => 'required|string|max:100',
            'ponto_referencia' => 'nullable|string|max:200',
            'relato_cliente' => 'required|string|min:10',
            'foto' => 'nullable|image|max:10240', // até 10MB
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        if (! self::validarCpf($validated['cpf'])) {
            return back()->withInput()->withErrors([
                'cpf' => 'O CPF informado não é válido. Verifique os dígitos informados e tente novamente.',
            ]);
        }

        $cpfLimpo = preg_replace('/\D/', '', $validated['cpf']);

        // Localiza ou cadastra o munícipe
        $cliente = Cliente::where('cpf', $cpfLimpo)
            ->orWhere('cpf', $validated['cpf'])
            ->first();

        if (! $cliente) {
            $cliente = Cliente::create([
                'tipo_pessoa' => 'F',
                'nome' => $validated['nome'],
                'cpf' => $cpfLimpo,
            ]);

            Contato::create([
                'cliente_id' => $cliente->id,
                'nome' => $validated['nome'],
                'telefone' => $validated['telefone'],
                'email' => $validated['email'] ?? null,
                'principal' => true,
            ]);
        }

        $categoria = CategoriaZeladoria::findOrFail($validated['categoria_zeladoria_id']);

        // Gera código oficial de rastreio consistente (ex: ZEL-2026/00005)
        $ano = (int) date('Y');
        $codigoProtocolo = OrdemServico::gerarCodigoZeladoria($ano);

        // Upload da foto antes, se houver
        $fotoAntesPath = null;
        if ($request->hasFile('foto') && $request->file('foto')->isValid()) {
            $fotoAntesPath = $request->file('foto')->store('zeladoria_publica', 'public');
        }

        $demanda = OrdemServico::create([
            'codigo_interno' => $codigoProtocolo,
            'chave_acesso' => Processo::novaChaveAcesso(),
            'cliente_id' => $cliente->id,
            'tipo_os' => 'Zeladoria Urbana',
            'origem_os' => 'Portal Cidadão',
            'categoria_zeladoria_id' => $categoria->id,
            'secretaria_responsavel_id' => $categoria->secretaria_padrao_id,
            'logradouro' => $validated['logradouro'],
            'numero_logradouro' => $validated['numero'] ?? 'S/N',
            'bairro' => $validated['bairro'],
            'referencia_local' => $validated['ponto_referencia'] ?? null,
            'relato_cliente' => $validated['relato_cliente'],
            'status' => 'Registrada / Triagem',
            'prioridade' => 'normal',
            'origem_demanda' => 'portal',
            'data_abertura' => Carbon::now(),
            'foto_antes' => $fotoAntesPath,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'prevista_conclusao' => now()->addDays($categoria->prazo_padrao_dias ?: 7),
        ]);

        return redirect()->route('portal.zeladoria.show', ['codigo' => $codigoProtocolo, 'chave' => $demanda->chave_acesso])
            ->with('success', "Sua solicitação de {$categoria->nome} foi protocolada com sucesso sob o nº {$codigoProtocolo}!");
    }

    /**
     * Acompanhamento público em tempo real da demanda de zeladoria pelo munícipe.
     */
    public function acompanharDemanda(Request $request, string $codigo)
    {
        $demanda = OrdemServico::with(['categoriaZeladoria', 'secretariaResponsavel', 'cliente'])
            ->whereNotNull('categoria_zeladoria_id')
            ->where('codigo_interno', $codigo)
            ->first();

        if (! $demanda) {
            return view('portal.zeladoria-status', [
                'demanda' => null,
                'codigo' => $codigo,
                'erro' => "Nenhum pedido de serviço encontrado com o código \"{$codigo}\".",
            ]);
        }

        // Sem a chave do comprovante, mostra só o andamento (sem relato, endereço exato, fotos ou nome)
        $chave = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $request->input('chave', '')));
        $completo = $demanda->chave_acesso && $chave !== '' && hash_equals((string) $demanda->chave_acesso, $chave);

        // Aplica mascaramento LGPD
        $dadosRequerente = [
            'nome' => self::mascararNome($demanda->cliente?->nome),
            'cpf' => self::mascararCpf($demanda->cliente?->cpf ?? $demanda->cliente?->cnpj),
        ];

        return view('portal.zeladoria-status', compact('demanda', 'codigo', 'dadosRequerente', 'completo'));
    }

    /**
     * Validação pública de autenticidade documental (Hash SHA-256 estrito, sem enumeração).
     */
    public function verificarAutenticidade(Request $request)
    {
        $termo = trim((string) $request->input('hash', $request->input('codigo', '')));
        $resultado = null;

        if (!empty($termo)) {
            // A verificação de autenticidade exige o Hash SHA-256 completo (64 caracteres)
            // Impede enumeração ID-based que vazaria processos sigilosos
            if (strlen($termo) < 32) {
                $resultado = [
                    'autentico' => false,
                    'hash' => $termo,
                    'mensagem' => 'O código verificador deve ser o Hash SHA-256 oficial completo impresso no rodapé do documento.',
                ];
            } else {
                $doc = ProcessoDocumento::with(['processo', 'assinadoPor', 'criador'])
                    ->where('hash_sha256', strtolower($termo))
                    ->orWhere('hash_sha256', strtoupper($termo))
                    ->first();

                if ($doc) {
                    $processo = $doc->processo;
                    $isSigiloso = $processo && in_array($processo->sigilo, ['sigiloso', 'restrito']);

                    $resultado = [
                        'autentico' => true,
                        'documento' => $doc,
                        'processo' => $isSigiloso ? null : $processo,
                        'is_sigiloso' => $isSigiloso,
                        'hash' => $doc->hash_sha256,
                        'data_emissao' => $doc->created_at,
                        'assinado' => $doc->assinado_digitalmente,
                        'assinado_por' => $doc->assinadoPor?->name ?? 'Servidor Oficial da Prefeitura',
                        'aviso_sigilo' => $isSigiloso ? 'Documento integrante de processo com restrição de sigilo legal (LAI nº 12.527/2011). Metadados protegidos.' : null,
                    ];
                } else {
                    $resultado = [
                        'autentico' => false,
                        'hash' => $termo,
                        'mensagem' => 'Aviso de Verificação: O Hash SHA-256 fornecido não confere com nenhum documento oficial emitido pelo sistema municipal Phoenix - SIGEM.',
                    ];
                }
            }
        }

        return view('portal.autenticidade', compact('termo', 'resultado'));
    }
}
