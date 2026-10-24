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
use App\Models\Motorista;
use App\Models\Veiculo;
use App\Support\StatusZeladoria;
use App\Models\UnidadeAdministrativa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Início do servidor: "Minha caixa" (o que está pendente com ele/sua secretaria),
     * indicadores neutros, últimas movimentações e mapa de ocorrências abertas.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $gestor = $user->ehGestorGeral();
        $unidadeId = $user->unidade_administrativa_id;
        $verSigilosos = $gestor || $user->hasPermission('protocolo.sigiloso');

        $caixa = collect();

        // 1) Processos que chegaram e aguardam recebimento
        $aReceber = Tramitacao::with(['processo.unidadeAtual', 'unidadeOrigem'])
            ->where('status', 'enviado')
            ->whereHas('processo', function ($q) use ($gestor, $unidadeId) {
                $q->whereIn('status', ['tramitando', 'suspenso'])
                  ->whereColumn('processos.unidade_atual_id', 'tramitacoes.unidade_destino_id');
                if (! $gestor) {
                    $q->where('unidade_atual_id', $unidadeId ?: 0);
                }
            })
            ->latest('data_envio')->limit(10)->get();
        foreach ($aReceber as $t) {
            $p = $t->processo;
            if (! $p) {
                continue;
            }
            $caixa->push([
                'tipo' => 'Processo',
                'codigo' => $p->numero,
                'url' => route('protocolo.show', $p),
                'titulo' => $this->assuntoVisivel($p, $verSigilosos, $unidadeId, $user->id),
                'detalhe' => 'Chegou' . ($t->unidadeOrigem ? ' da ' . ($t->unidadeOrigem->sigla ?: $t->unidadeOrigem->nome) : '') . ' ' . optional($t->data_envio)->diffForHumans() . ($gestor ? ' · ' . ($p->unidadeAtual->sigla ?? '') : ''),
                'status' => 'A receber',
                'tom' => 'info',
                'acao' => 'Receber',
                'primaria' => true,
                'peso' => $p->prioridade === 'urgente' ? 0 : 1,
            ]);
        }

        // 2) Processos com o servidor (recebidos por ele) ainda em andamento
        $comigo = Processo::whereIn('status', ['tramitando', 'suspenso'])
            ->where('usuario_responsavel_id', $user->id)
            ->orderByRaw('data_limite IS NULL')->orderBy('data_limite')
            ->limit(10)->get();
        foreach ($comigo as $p) {
            $dias = $p->dias_para_prazo;
            [$status, $tom] = match (true) {
                $dias === null => [$p->status === 'suspenso' ? 'Suspenso' : 'Em andamento', $p->status === 'suspenso' ? 'warn' : 'neutral'],
                $dias < 0 => ['Prazo vencido', 'danger'],
                $dias <= 3 => [$dias === 0 ? 'Vence hoje' : "Vence em {$dias} " . ($dias === 1 ? 'dia' : 'dias'), 'warn'],
                default => ['Prazo ' . $p->data_limite->format('d/m'), 'neutral'],
            };
            $caixa->push([
                'tipo' => 'Processo',
                'codigo' => $p->numero,
                'url' => route('protocolo.show', $p),
                'titulo' => $p->assunto,
                'detalhe' => 'Com você' . ($p->data_limite ? ' · prazo ' . $p->data_limite->format('d/m/Y') : ''),
                'status' => $status,
                'tom' => $tom,
                'acao' => 'Despachar',
                'primaria' => false,
                'peso' => $tom === 'danger' ? 0 : ($tom === 'warn' ? 1 : 3),
            ]);
        }

        // 3) Requisições aguardando aprovação desta chefia
        if ($gestor || $user->hasPermission('almoxarifado.aprovar')) {
            $paraAprovar = RequisicaoMaterial::with(['unidadeSolicitante', 'itens.produto'])
                ->where('situacao', 'pendente_aprovacao')
                ->when(! $gestor, fn ($q) => $q->where('unidade_solicitante_id', $unidadeId ?: 0))
                ->where('solicitante_user_id', '!=', $user->id)
                ->latest('id')->limit(8)->get();
            foreach ($paraAprovar as $r) {
                $caixa->push([
                    'tipo' => 'Requisição',
                    'codigo' => $r->numero,
                    'url' => route('almoxarifado.requisicoes.show', $r),
                    'titulo' => $this->resumoItens($r),
                    'detalhe' => 'Solicitada por ' . ($r->unidadeSolicitante->sigla ?? $r->unidadeSolicitante->nome ?? '—') . ' ' . $r->created_at->diffForHumans(),
                    'status' => 'Aguarda aprovação',
                    'tom' => $r->prioridade === 'urgente' ? 'danger' : 'info',
                    'acao' => 'Analisar',
                    'primaria' => false,
                    'peso' => $r->prioridade === 'urgente' ? 0 : 2,
                ]);
            }
        }

        // 4) Requisições aprovadas aguardando o almoxarife
        if ($gestor || $user->hasPermission('almoxarifado.atender')) {
            $paraAtender = RequisicaoMaterial::with(['unidadeSolicitante', 'almoxarifado', 'itens.produto'])
                ->whereIn('situacao', ['aprovada', 'atendida_parcial'])
                ->when(! $gestor, fn ($q) => $q->whereHas('almoxarifado', fn ($d) => $d->where('unidade_administrativa_id', $unidadeId ?: 0)->orWhereNull('unidade_administrativa_id')))
                ->latest('aprovado_em')->limit(8)->get();
            foreach ($paraAtender as $r) {
                $caixa->push([
                    'tipo' => 'Requisição',
                    'codigo' => $r->numero,
                    'url' => route('almoxarifado.requisicoes.show', $r),
                    'titulo' => $this->resumoItens($r),
                    'detalhe' => 'Aprovada · separar para ' . ($r->unidadeSolicitante->sigla ?? '—') . ' no ' . ($r->almoxarifado->nome ?? 'almoxarifado'),
                    'status' => $r->situacao === 'atendida_parcial' ? 'Entrega parcial' : 'Separar material',
                    'tom' => 'info',
                    'acao' => 'Atender',
                    'primaria' => false,
                    'peso' => 2,
                ]);
            }
        }

        // 5) Demandas de zeladoria da secretaria sem programação
        $zelQuery = OrdemServico::with(['categoriaZeladoria'])
            ->whereNotNull('categoria_zeladoria_id')
            ->whereIn('status', StatusZeladoria::ABERTAS)
            ->when(! $gestor, fn ($q) => $q->where('secretaria_responsavel_id', $unidadeId ?: 0));
        foreach ($zelQuery->oldest('data_abertura')->limit(8)->get() as $os) {
            $abertaHa = $os->data_abertura ? Carbon::parse($os->data_abertura) : $os->created_at;
            $atrasada = $os->prevista_conclusao && Carbon::parse($os->prevista_conclusao)->isPast();
            $caixa->push([
                'tipo' => 'Zeladoria',
                'codigo' => $os->codigo_interno ?: ('#' . $os->id),
                'url' => route('zeladoria.show', $os),
                'titulo' => ($os->categoriaZeladoria->nome ?? 'Demanda') . ' — ' . trim(($os->logradouro ?? '') . ($os->bairro ? ', ' . $os->bairro : ''), ' ,'),
                'detalhe' => 'Aberta ' . $abertaHa->diffForHumans() . ' · ' . $this->origemLegivel($os->origem_demanda),
                'status' => $atrasada ? 'Atrasada' : 'Em triagem',
                'tom' => $atrasada ? 'danger' : 'info',
                'acao' => 'Programar',
                'primaria' => false,
                'peso' => $atrasada ? 0 : 2,
            ]);
        }

        $caixa = $caixa->sortBy('peso')->values();

        // Indicadores (escopo: minha secretaria; gestor vê o município)
        $procBase = Processo::whereIn('status', ['tramitando', 'suspenso'])->when(! $gestor && $unidadeId, fn ($q) => $q->where('unidade_atual_id', $unidadeId));
        $zelBase = OrdemServico::whereNotNull('categoria_zeladoria_id')->when(! $gestor && $unidadeId, fn ($q) => $q->where('secretaria_responsavel_id', $unidadeId));
        $indicadores = [
            'processos' => (clone $procBase)->count(),
            'processos_urgentes' => (clone $procBase)->where('prioridade', 'urgente')->count(),
            'zeladoria_abertas' => (clone $zelBase)->whereIn('status', StatusZeladoria::ABERTAS)->count(),
            'zeladoria_campo' => (clone $zelBase)->whereIn('status', StatusZeladoria::EM_CAMPO)->count(),
            'requisicoes_pendentes' => RequisicaoMaterial::where('situacao', 'pendente_aprovacao')->when(! $gestor && $unidadeId, fn ($q) => $q->where('unidade_solicitante_id', $unidadeId))->count(),
            'estoque_critico' => Produto::whereNull('deleted_at')->whereRaw('estoque_quantidade <= estoque_minimo')->where('estoque_minimo', '>', 0)->count(),
            'veiculos_viagem' => Veiculo::where('status', 'em_viagem')->count(),
            'cnh_vencendo' => Motorista::whereNotNull('cnh_validade')->whereBetween('cnh_validade', [now()->startOfDay(), now()->addDays(30)])->count(),
        ];

        // Últimas movimentações de processos (sem expor sigilosos)
        $movimentacoes = Tramitacao::with(['processo', 'unidadeOrigem', 'unidadeDestino', 'usuarioEnvio'])
            ->whereHas('processo', function ($q) use ($verSigilosos, $unidadeId) {
                if (! $verSigilosos) {
                    $q->where(fn ($s) => $s->where('sigilo', 'publico')->when($unidadeId, fn ($x) => $x->orWhere('unidade_atual_id', $unidadeId)));
                }
            })
            ->latest('id')->limit(6)->get();

        // Processos em andamento por secretaria
        $porSecretaria = UnidadeAdministrativa::where('ativo', true)->orderBy('sigla')->get()
            ->map(fn ($u) => ['sigla' => $u->sigla ?: $u->nome, 'nome' => $u->nome, 'total' => 0, 'id' => $u->id])
            ->keyBy('id');
        Processo::whereIn('status', ['tramitando', 'suspenso'])
            ->select('unidade_atual_id', DB::raw('count(*) as total'))->groupBy('unidade_atual_id')->get()
            ->each(function ($r) use ($porSecretaria) {
                if ($porSecretaria->has($r->unidade_atual_id)) {
                    $linha = $porSecretaria->get($r->unidade_atual_id);
                    $linha['total'] = (int) $r->total;
                    $porSecretaria->put($r->unidade_atual_id, $linha);
                }
            });
        $porSecretaria = $porSecretaria->values()->sortByDesc('total')->take(8)->values();
        $maiorCarga = max(1, (int) $porSecretaria->max('total'));

        // Mapa: ocorrências de zeladoria ainda não encerradas
        $ocorrenciasMapa = OrdemServico::whereNotNull('latitude')->whereNotNull('longitude')
            ->whereNotNull('categoria_zeladoria_id')
            ->whereNotIn('status', StatusZeladoria::ENCERRADAS)
            ->with('categoriaZeladoria')->latest('id')->limit(80)->get()
            ->map(fn ($os) => [
                'lat' => (float) $os->latitude,
                'lng' => (float) $os->longitude,
                'codigo' => $os->codigo_interno ?: ('#' . $os->id),
                'categoria' => $os->categoriaZeladoria->nome ?? 'Zeladoria',
                'local' => trim(($os->logradouro ?? '') . ($os->bairro ? ' — ' . $os->bairro : '')),
                'situacao' => StatusZeladoria::rotulo($os->status),
                'url' => route('zeladoria.show', $os),
            ])->values();

        return erp_view('pages.dashboard.index', [
            'title' => 'Início',
            'saudacao' => $this->saudacao(),
            'lotacao' => $user->unidadeAdministrativa,
            'gestor' => $gestor,
            'caixa' => $caixa,
            'indicadores' => $indicadores,
            'movimentacoes' => $movimentacoes,
            'porSecretaria' => $porSecretaria,
            'maiorCarga' => $maiorCarga,
            'ocorrenciasMapa' => $ocorrenciasMapa,
        ]);
    }

    protected function saudacao(): string
    {
        $h = (int) now()->format('H');

        return $h < 12 ? 'Bom dia' : ($h < 18 ? 'Boa tarde' : 'Boa noite');
    }

    protected function assuntoVisivel(Processo $p, bool $verSigilosos, $unidadeId, $userId): string
    {
        if ($p->sigilo === 'publico' || $verSigilosos || (int) $p->unidade_atual_id === (int) $unidadeId || (int) $p->created_by === (int) $userId) {
            return $p->assunto;
        }

        return 'Processo ' . mb_strtolower($p->sigilo_rotulo);
    }

    protected function resumoItens(RequisicaoMaterial $r): string
    {
        $itens = $r->itens;
        if ($itens->isEmpty()) {
            return 'Requisição sem itens';
        }
        $primeiro = $itens->first();
        $texto = rtrim(rtrim(number_format((float) $primeiro->quantidade_solicitada, 2, ',', '.'), '0'), ',') . ' ' . ($primeiro->produto->unidade ?? 'un') . ' de ' . ($primeiro->produto->nome ?? 'material');

        return $itens->count() > 1 ? $texto . ' e mais ' . ($itens->count() - 1) . ' ' . ($itens->count() === 2 ? 'item' : 'itens') : $texto;
    }

    protected function origemLegivel(?string $origem): string
    {
        return match ($origem) {
            'portal' => 'pelo portal',
            'telefone_156' => 'pelo 156',
            'balcao' => 'no balcão',
            'ouvidoria' => 'pela ouvidoria',
            'fiscalizacao' => 'pela fiscalização',
            'processo_protocolo' => 'por processo',
            default => 'registrada internamente',
        };
    }

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
