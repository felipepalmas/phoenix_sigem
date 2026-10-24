<?php

/**
 * Componente da aplicação Phoenix - SIGEM
 * Centro de Comando e Gestão Municipal Integrada
 *
 * @author    viniciusvams / Phoenix - SIGEM
 * @copyright 2024-2026 LivreOS / Phoenix
 * @license   https://www.gnu.org/licenses/agpl-3.0.txt AGPL-3.0
 */

namespace App\Http\Controllers;

use App\Models\CategoriaZeladoria;
use App\Models\Cliente;
use App\Models\MovimentacaoEstoque;
use App\Models\OrdemServico;
use App\Models\Processo;
use App\Models\Produto;
use App\Models\RequisicaoMaterial;
use App\Models\Tramitacao;
use App\Models\UnidadeAdministrativa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = now()->toDateString();

        // 1. KPIs do Centro de Comando Municipal
        // 1.1 Protocolo Digital (Zero Papel)
        $kpiProcessos = [
            'total'       => Processo::count(),
            'tramitando'  => Processo::where('status', 'tramitando')->count(),
            'urgentes'    => Processo::where('status', 'tramitando')->where('prioridade', 'urgente')->count(),
            'concluidos'  => Processo::whereIn('status', ['deferido', 'indeferido', 'concluido', 'arquivado'])->count(),
        ];

        // 1.2 Zeladoria Urbana e Serviços Públicos
        $zeladoriaBase = OrdemServico::where(function ($q) {
            $q->whereNotNull('categoria_zeladoria_id')
              ->orWhereNotNull('logradouro')
              ->orWhere('tipo_os', 'Zeladoria Urbana');
        });
        $kpiZeladoria = [
            'total'       => (clone $zeladoriaBase)->count(),
            'abertas'     => (clone $zeladoriaBase)->where(function ($q) {
                $q->where('status', 'like', '%Aberta%')
                  ->orWhere('status', 'like', '%Registrada%')
                  ->orWhere('status', 'like', '%Triagem%');
            })->count(),
            'em_campo'    => (clone $zeladoriaBase)->where(function ($q) {
                $q->where('status', 'like', '%Execução%')
                  ->orWhere('status', 'like', '%Programada%')
                  ->orWhere('status', 'like', '%Em andamento%');
            })->count(),
            'concluidas'  => (clone $zeladoriaBase)->where(function ($q) {
                $q->where('status', 'like', '%Concluída%')
                  ->orWhere('status', 'like', '%Encerrada%')
                  ->orWhere('status', 'like', '%Encerrado%');
            })->count(),
        ];

        // 1.3 Almoxarifado Setorial e Suprimentos
        $kpiAlmoxarifado = [
            'total_requisicoes'   => RequisicaoMaterial::count(),
            'pendente_aprovacao'  => RequisicaoMaterial::where('situacao', 'pendente_aprovacao')->count(),
            'aprovadas'           => RequisicaoMaterial::where('situacao', 'aprovada')->count(),
            'atendidas'           => RequisicaoMaterial::whereIn('situacao', ['atendida', 'concluida'])->count(),
            'estoque_critico'     => Produto::whereNull('deleted_at')->whereRaw('estoque_quantidade <= estoque_minimo')->where('estoque_minimo', '>', 0)->count(),
        ];

        // 1.4 Estrutura Municipal & Contribuintes
        $kpiGeral = [
            'total_cidadaos'      => Cliente::count(),
            'total_secretarias'   => UnidadeAdministrativa::where('ativo', true)->count(),
            'total_materiais'     => Produto::whereNull('deleted_at')->count(),
        ];

        // 2. Mapa Operacional Georreferenciado de Zeladoria Urbana
        $ocorrenciasMapa = OrdemServico::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with(['cliente', 'categoriaZeladoria'])
            ->latest('id')
            ->limit(50)
            ->get()
            ->map(function ($os) {
                return [
                    'id'          => $os->id,
                    'codigo'      => $os->codigo_interno ?: ('OS #' . $os->id),
                    'lat'         => (float) $os->latitude,
                    'lng'         => (float) $os->longitude,
                    'bairro'      => $os->bairro ?: 'Bairro não informado',
                    'logradouro'  => $os->logradouro ?: 'Logradouro não informado',
                    'categoria'   => $os->categoriaZeladoria?->nome ?: 'Geral / Manutenção',
                    'cor'         => $os->categoriaZeladoria?->cor ?: '#0284c7',
                    'status'      => $os->status,
                    'prioridade'  => $os->prioridade,
                    'data'        => $os->data_abertura ? Carbon::parse($os->data_abertura)->format('d/m/Y') : $os->created_at->format('d/m/Y'),
                    'relato'      => $os->relato_cliente ?: ($os->descricao_problema ?: 'Sem descrição'),
                    'show_url'    => route('zeladoria.show', $os),
                ];
            });

        // 3. Distribuição de Processos por Secretaria (Carga de Trabalho / Gargalos)
        $secretarias = UnidadeAdministrativa::where('ativo', true)->orderBy('nome')->get();
        $processosPorUnidade = Processo::where('status', 'tramitando')
            ->select('unidade_atual_id', DB::raw('count(*) as total'))
            ->groupBy('unidade_atual_id')
            ->pluck('total', 'unidade_atual_id')
            ->all();

        $secretariasCarga = $secretarias->map(function ($sec) use ($processosPorUnidade) {
            return [
                'id'       => $sec->id,
                'nome'     => $sec->nome,
                'sigla'    => $sec->sigla ?: $sec->nome,
                'total'    => $processosPorUnidade[$sec->id] ?? 0,
            ];
        })->sortByDesc('total')->values();

        // 4. Distribuição de Ocorrências por Categoria de Zeladoria
        $categoriasZeladoria = CategoriaZeladoria::withCount('ordensServico')
            ->orderByDesc('ordens_servico_count')
            ->limit(6)
            ->get();

        // 5. Linha do Tempo de Atividades em Tempo Real (Feed do Município)
        $ultimasTramitacoes = Tramitacao::with(['processo', 'unidadeOrigem', 'unidadeDestino', 'usuarioEnvio'])
            ->latest('id')
            ->limit(6)
            ->get();

        $ultimasDemandas = (clone $zeladoriaBase)->with(['categoriaZeladoria', 'cliente'])
            ->latest('id')
            ->limit(6)
            ->get();

        $ultimasRequisicoes = RequisicaoMaterial::with(['unidadeSolicitante', 'almoxarifado', 'solicitante'])
            ->latest('id')
            ->limit(5)
            ->get();

        // 6. Processos Urgentes Requerendo Despacho
        $processosPrioritarios = Processo::where('status', 'tramitando')
            ->with(['interessado', 'unidadeAtual'])
            ->orderByRaw("CASE WHEN prioridade = 'urgente' THEN 1 ELSE 2 END")
            ->latest('updated_at')
            ->limit(5)
            ->get();

        // 7. Dados da agenda de serviços (mantido para compatibilidade)
        $ano = (int) $request->input('agenda_ano', now()->year);
        $mes = (int) $request->input('agenda_mes', now()->month);
        $agendaData = $this->getAgendaData($mes, $ano);

        $viewData = [
            'title'                 => 'Centro de Comando Municipal',
            'kpiProcessos'          => $kpiProcessos,
            'kpiZeladoria'          => $kpiZeladoria,
            'kpiAlmoxarifado'       => $kpiAlmoxarifado,
            'kpiGeral'              => $kpiGeral,
            'ocorrenciasMapa'       => $ocorrenciasMapa,
            'secretariasCarga'      => $secretariasCarga,
            'categoriasZeladoria'   => $categoriasZeladoria,
            'ultimasTramitacoes'    => $ultimasTramitacoes,
            'ultimasDemandas'       => $ultimasDemandas,
            'ultimasRequisicoes'    => $ultimasRequisicoes,
            'processosPrioritarios' => $processosPrioritarios,
            // Compatibilidade
            'agendaPorDia'          => $agendaData['agendaPorDia'],
            'agendaParaModal'       => $agendaData['agendaParaModal'],
            'agendaPendentes'       => $agendaData['agendaPendentes'],
            'agendaMesAno'          => $agendaData['agendaMesAno'],
            'agendaMesMes'          => $agendaData['agendaMesMes'],
            'agendaMesNome'         => $agendaData['mesNome'],
            'agendaDiasNoMes'       => $agendaData['diasNoMes'],
            'agendaPrimeiroDiaSemana' => $agendaData['primeiroDiaSemana'],
            'agendaMesAnoPrev'      => $agendaData['agendaMesAnoPrev'],
            'agendaMesMesPrev'      => $agendaData['agendaMesMesPrev'],
            'agendaMesAnoNext'      => $agendaData['agendaMesAnoNext'],
            'agendaMesMesNext'      => $agendaData['agendaMesMesNext'],
            'agendaPrimeiroDia'     => $agendaData['agendaPrimeiroDia'],
            'ultimasOs'             => $ultimasDemandas,
            'totalClientes'         => $kpiGeral['total_cidadaos'],
            'totalProdutos'         => $kpiGeral['total_materiais'],
            'totalOrdens'           => $kpiZeladoria['total'],
        ];

        if (function_exists('apply_filters')) {
            $viewData = apply_filters('dashboard.main.data', $viewData);
        }

        return erp_view('pages.dashboard.index', $viewData);
    }

    /** Status de OS considerados "ativos" (aparecem na agenda). */
    protected const AGENDA_STATUS_EXCLUIDOS = ['Encerrada', 'Encerrado', 'Cancelada', 'Cancelado', 'Concluída'];

    /**
     * Retorna dados da agenda das OS para um mês/ano (usado no index e no endpoint JSON).
     */
    protected function getAgendaData(int $mes, int $ano): array
    {
        $primeiroDia = Carbon::createFromDate($ano, $mes, 1)->startOfDay();
        $ultimoDia = $primeiroDia->copy()->endOfMonth();
        $inicioStr = $primeiroDia->toDateString();
        $fimStr = $ultimoDia->toDateString();

        $agendaOsQuery = OrdemServico::with(['cliente'])
            ->whereNotIn('status', self::AGENDA_STATUS_EXCLUIDOS)
            ->where(function ($q) use ($inicioStr, $fimStr) {
                $q->whereBetween(DB::raw('DATE(prevista_conclusao)'), [$inicioStr, $fimStr])
                    ->orWhereBetween(DB::raw('DATE(prevista_inicio)'), [$inicioStr, $fimStr])
                    ->orWhereBetween(DB::raw('DATE(data_abertura)'), [$inicioStr, $fimStr]);
            })
            ->get();

        $osPendentes = OrdemServico::with(['cliente'])
            ->whereNotIn('status', self::AGENDA_STATUS_EXCLUIDOS)
            ->where(function ($q) use ($inicioStr) {
                $q->where(function ($q2) use ($inicioStr) {
                    $q2->whereNotNull('prevista_conclusao')
                        ->where(DB::raw('DATE(prevista_conclusao)'), '<', $inicioStr);
                })->orWhere(function ($q2) use ($inicioStr) {
                    $q2->whereNull('prevista_conclusao')
                        ->whereNotNull('prevista_inicio')
                        ->where(DB::raw('DATE(prevista_inicio)'), '<', $inicioStr);
                })->orWhere(function ($q2) use ($inicioStr) {
                    $q2->whereNull('prevista_conclusao')
                        ->whereNull('prevista_inicio')
                        ->whereNotNull('data_abertura')
                        ->where(DB::raw('DATE(data_abertura)'), '<', $inicioStr);
                });
            })
            ->orderBy('data_abertura')
            ->get();

        $agendaPorDia = [];
        foreach ($agendaOsQuery as $os) {
            $dataRef = $os->prevista_conclusao ?? $os->prevista_inicio ?? $os->data_abertura;
            if (!$dataRef) {
                continue;
            }
            $dataRef = $dataRef instanceof \Carbon\Carbon ? $dataRef : Carbon::parse($dataRef);
            $chave = $dataRef->format('Y-m-d');
            if ($chave < $inicioStr || $chave > $fimStr) {
                continue;
            }
            if (!isset($agendaPorDia[$chave])) {
                $agendaPorDia[$chave] = [];
            }
            $agendaPorDia[$chave][] = $os;
        }

        $agendaParaModal = [];
        foreach ($agendaPorDia as $chave => $osList) {
            $agendaParaModal[$chave] = [];
            foreach ($osList as $os) {
                $agendaParaModal[$chave][] = [
                    'id' => $os->id,
                    'codigo' => function_exists('format_os_display') ? format_os_display($os) : ($os->codigo_interno ?? 'OS #' . $os->id),
                    'cliente' => $os->cliente ? ($os->cliente->nome ?? $os->cliente->razao_social ?? '—') : '—',
                    'status' => $os->status,
                    'data_abertura' => $os->data_abertura ? Carbon::parse($os->data_abertura)->format('d/m/Y') : null,
                    'show_url' => route('ordens-servico.show', $os),
                ];
            }
        }

        $pendentesFormatados = [];
        foreach ($osPendentes as $os) {
            $dataRef = $os->prevista_conclusao ?? $os->prevista_inicio ?? $os->data_abertura;
            $pendentesFormatados[] = [
                'id' => $os->id,
                'codigo' => function_exists('format_os_display') ? format_os_display($os) : ($os->codigo_interno ?? 'OS #' . $os->id),
                'cliente' => $os->cliente ? ($os->cliente->nome ?? $os->cliente->razao_social ?? '—') : '—',
                'status' => $os->status,
                'data_ref' => $dataRef ? ($dataRef instanceof \Carbon\Carbon ? $dataRef->format('d/m/Y') : Carbon::parse($dataRef)->format('d/m/Y')) : null,
                'show_url' => route('ordens-servico.show', $os),
            ];
        }

        $meses = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
        ];

        $prev = $primeiroDia->copy()->subMonth();
        $next = $primeiroDia->copy()->addMonth();

        return [
            'mesNome' => $meses[$mes] ?? '',
            'ano' => $ano,
            'mes' => $mes,
            'diasNoMes' => $primeiroDia->daysInMonth,
            'primeiroDiaSemana' => $primeiroDia->dayOfWeek,
            'agendaPorDia' => $agendaPorDia,
            'agendaParaModal' => $agendaParaModal,
            'agendaPendentes' => $pendentesFormatados,
            'agendaMesAno' => $ano,
            'agendaMesMes' => $mes,
            'agendaMesAnoPrev' => $prev->year,
            'agendaMesMesPrev' => $prev->month,
            'agendaMesAnoNext' => $next->year,
            'agendaMesMesNext' => $next->month,
            'agendaPrimeiroDia' => $primeiroDia,
        ];
    }

    /**
     * API da agenda das OS (mês/ano) para atualização sem recarregar a página.
     */
    public function agendaApi(Request $request)
    {
        $ano = (int) $request->input('agenda_ano', now()->year);
        $mes = (int) $request->input('agenda_mes', now()->month);
        $mes = max(1, min(12, $mes));
        $data = $this->getAgendaData($mes, $ano);
        return response()->json([
            'mesNome' => $data['mesNome'],
            'ano' => $data['ano'],
            'mes' => $data['mes'],
            'diasNoMes' => $data['diasNoMes'],
            'primeiroDiaSemana' => $data['primeiroDiaSemana'],
            'agendaParaModal' => $data['agendaParaModal'],
            'agendaPendentes' => $data['agendaPendentes'],
            'agendaMesAnoPrev' => $data['agendaMesAnoPrev'],
            'agendaMesMesPrev' => $data['agendaMesMesPrev'],
            'agendaMesAnoNext' => $data['agendaMesAnoNext'],
            'agendaMesMesNext' => $data['agendaMesMesNext'],
        ]);
    }
}
