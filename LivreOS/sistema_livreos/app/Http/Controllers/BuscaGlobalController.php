<?php

/**
 * Componente da aplicação Phoenix - SIGEM
 * Ferramenta Central de Busca Global Municipal com Hierarquia de Permissões
 *
 * @license AGPL-3.0
 */

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\OrdemServico;
use App\Models\Processo;
use App\Models\Produto;
use App\Models\RequisicaoMaterial;
use App\Models\UnidadeAdministrativa;
use App\Models\Veiculo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BuscaGlobalController extends Controller
{
    /**
     * Mapeia as permissões do usuário logado para definir a quais
     * módulos ele tem acesso na busca.
     */
    protected function obterPermissoesUsuario(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [
                'protocolo' => false,
                'zeladoria' => false,
                'almoxarifado' => false,
                'cidadaos' => false,
                'secretarias' => false,
                'frota' => false,
                'isPortalCidadao' => false,
                'clienteId' => null,
            ];
        }

        $isAdmin = (bool) $user->is_admin || $user->hasRole('admin');
        $isManager = $isAdmin || $user->hasRole('manager');
        $isTecnico = $user->hasRole('technician');
        $isAtendente = $user->hasRole('attendant');
        $isOperacional = $user->canAccessOperational();
        $isPortalCidadao = ! $isOperacional && (bool) $user->cliente_id;

        return [
            'isAdmin' => $isAdmin,
            'isManager' => $isManager,
            'isTecnico' => $isTecnico,
            'isAtendente' => $isAtendente,
            'isOperacional' => $isOperacional,
            'isPortalCidadao' => $isPortalCidadao,
            'clienteId' => $user->cliente_id,
            // Acesso por módulo
            'protocolo' => $isAdmin || $isManager || $isAtendente || $isOperacional || $isPortalCidadao,
            'zeladoria' => $isAdmin || $isManager || $isTecnico || $isAtendente || $isOperacional || $isPortalCidadao,
            'almoxarifado' => $isAdmin || $isManager || $isTecnico || ($isOperacional && ! $isPortalCidadao),
            'cidadaos' => $isAdmin || $isManager || $isAtendente || ($isOperacional && ! $isPortalCidadao && $user->hasPermission('view-clients')),
            'secretarias' => $isAdmin || $isManager || ($isOperacional && ! $isPortalCidadao),
            'frota' => $isAdmin || $isManager || $isTecnico || ($isOperacional && ! $isPortalCidadao),
        ];
    }

    /**
     * Retorna a página visual completa de resultados de busca global.
     */
    public function index(Request $request)
    {
        $termo = trim((string) $request->input('q', $request->input('busca', '')));
        $perm = $this->obterPermissoesUsuario();

        $resultados = [
            'processos' => collect(),
            'zeladoria' => collect(),
            'requisicoes' => collect(),
            'materiais' => collect(),
            'cidadaos' => collect(),
            'secretarias' => collect(),
            'veiculos' => collect(),
        ];

        if ($termo !== '') {
            // 1. Processos / Protocolo Digital (Zero Papel)
            if ($perm['protocolo']) {
                $qProc = Processo::with(['interessado', 'unidadeAtual']);
                if ($perm['isPortalCidadao']) {
                    $qProc->where('interessado_id', $perm['clienteId'])
                          ->whereNotIn('sigilo', ['sigiloso', 'restrito']);
                } else {
                    $user = auth()->user();
                    $canViewAllSigilosos = $perm['isAdmin'] || $perm['isManager'] || ($user && $user->hasPermission('protocolo.sigiloso'));
                    if (! $canViewAllSigilosos) {
                        $qProc->where(function ($sig) use ($user) {
                            $sig->where('sigilo', 'publico');
                            if ($user) {
                                $sig->orWhere('created_by', $user->id);
                                if ($user->unidade_administrativa_id) {
                                    $sig->orWhere('unidade_atual_id', $user->unidade_administrativa_id);
                                }
                            }
                        });
                    }
                }
                $qProc->where(function ($sub) use ($termo) {
                    $sub->where('numero', 'like', "%{$termo}%")
                        ->orWhere('assunto', 'like', "%{$termo}%")
                        ->orWhere('descricao', 'like', "%{$termo}%")
                        ->orWhereHas('interessado', function ($cli) use ($termo) {
                            $cli->where('nome', 'like', "%{$termo}%")
                                ->orWhere('razao_social', 'like', "%{$termo}%")
                                ->orWhere('cpf', 'like', "%{$termo}%")
                                ->orWhere('cnpj', 'like', "%{$termo}%");
                        });
                });
                $resultados['processos'] = $qProc->latest('id')->take(20)->get();
            }

            // 2. Zeladoria Urbana & Serviços Públicos
            if ($perm['zeladoria']) {
                $qZel = OrdemServico::with(['cliente', 'categoriaZeladoria', 'secretariaResponsavel'])
                    ->where(function ($z) {
                        $z->whereNotNull('categoria_zeladoria_id')
                          ->orWhere('tipo_os', 'Zeladoria Urbana')
                          ->orWhereNotNull('logradouro');
                    });
                if ($perm['isPortalCidadao']) {
                    $qZel->where('cliente_id', $perm['clienteId']);
                }
                $qZel->where(function ($sub) use ($termo) {
                    $sub->where('codigo_interno', 'like', "%{$termo}%")
                        ->orWhere('logradouro', 'like', "%{$termo}%")
                        ->orWhere('bairro', 'like', "%{$termo}%")
                        ->orWhere('relato_cliente', 'like', "%{$termo}%")
                        ->orWhereHas('cliente', function ($cli) use ($termo) {
                            $cli->where('nome', 'like', "%{$termo}%");
                        });
                });
                $resultados['zeladoria'] = $qZel->latest('id')->take(20)->get();
            }

            // 3. Almoxarifado Municipal (Requisições e Materiais)
            if ($perm['almoxarifado']) {
                $qReq = RequisicaoMaterial::with(['unidadeSolicitante', 'solicitante'])
                    ->where(function ($sub) use ($termo) {
                        $sub->where('numero', 'like', "%{$termo}%")
                            ->orWhere('justificativa', 'like', "%{$termo}%")
                            ->orWhereHas('unidadeSolicitante', function ($sec) use ($termo) {
                                $sec->where('nome', 'like', "%{$termo}%")
                                    ->orWhere('sigla', 'like', "%{$termo}%");
                            });
                    });
                $resultados['requisicoes'] = $qReq->latest('id')->take(10)->get();

                $qMat = Produto::whereNull('deleted_at')->where(function ($sub) use ($termo) {
                    $sub->where('nome', 'like', "%{$termo}%")
                        ->orWhere('codigo_sku', 'like', "%{$termo}%")
                        ->orWhere('ean', 'like', "%{$termo}%");
                });
                $resultados['materiais'] = $qMat->take(10)->get();
            }

            // 4. Cidadãos & Contribuintes
            if ($perm['cidadaos']) {
                $qCid = Cliente::where(function ($sub) use ($termo) {
                    $sub->where('nome', 'like', "%{$termo}%")
                        ->orWhere('razao_social', 'like', "%{$termo}%")
                        ->orWhere('cpf', 'like', "%{$termo}%")
                        ->orWhere('cnpj', 'like', "%{$termo}%")
                        ->orWhereHas('contatos', function ($c) use ($termo) {
                            $c->where('email', 'like', "%{$termo}%")
                              ->orWhere('telefone', 'like', "%{$termo}%");
                        });
                });
                $resultados['cidadaos'] = $qCid->take(15)->get();
            }

            // 5. Secretarias e Setores Administrativos
            if ($perm['secretarias']) {
                $qSec = UnidadeAdministrativa::where(function ($sub) use ($termo) {
                    $sub->where('nome', 'like', "%{$termo}%")
                        ->orWhere('sigla', 'like', "%{$termo}%")
                        ->orWhereHas('responsavel', function ($u) use ($termo) {
                            $u->where('name', 'like', "%{$termo}%");
                        });
                });
                $resultados['secretarias'] = $qSec->take(10)->get();
            }

            // 6. Frota Municipal & Veículos Oficiais
            if ($perm['frota']) {
                $qFrota = Veiculo::with('unidadeAdministrativa')
                    ->where('ativo', true)
                    ->where(function ($sub) use ($termo) {
                        $sub->where('placa', 'like', "%{$termo}%")
                            ->orWhere('modelo', 'like', "%{$termo}%")
                            ->orWhere('marca', 'like', "%{$termo}%")
                            ->orWhere('tipo', 'like', "%{$termo}%")
                            ->orWhere('patrimonio_tombamento', 'like', "%{$termo}%")
                            ->orWhere('renavam', 'like', "%{$termo}%");
                    });
                $resultados['veiculos'] = $qFrota->take(10)->get();
            }
        }

        $totalEncontrado = $resultados['processos']->count()
            + $resultados['zeladoria']->count()
            + $resultados['requisicoes']->count()
            + $resultados['materiais']->count()
            + $resultados['cidadaos']->count()
            + $resultados['secretarias']->count()
            + $resultados['veiculos']->count();

        return view('pages.busca.index', [
            'termo' => $termo,
            'resultados' => $resultados,
            'permissoes' => $perm,
            'totalEncontrado' => $totalEncontrado,
        ]);
    }

    /**
     * Endpoint API JSON para a busca rápida instantânea (autocomplete) no cabeçalho.
     */
    public function api(Request $request): JsonResponse
    {
        $termo = trim((string) $request->input('q', ''));

        if (mb_strlen($termo) < 2) {
            return response()->json([
                'total' => 0,
                'sections' => [],
            ]);
        }

        $perm = $this->obterPermissoesUsuario();
        $sections = [];
        $totalGeral = 0;

        // 1. Processos (Zero Papel)
        if ($perm['protocolo']) {
            $qProc = Processo::with(['interessado', 'unidadeAtual']);
            if ($perm['isPortalCidadao']) {
                $qProc->where('interessado_id', $perm['clienteId'])
                      ->whereNotIn('sigilo', ['sigiloso', 'restrito']);
            } else {
                $user = auth()->user();
                $canViewAllSigilosos = $perm['isAdmin'] || $perm['isManager'] || ($user && $user->hasPermission('protocolo.sigiloso'));
                if (! $canViewAllSigilosos) {
                    $qProc->where(function ($sig) use ($user) {
                        $sig->where('sigilo', 'publico');
                        if ($user) {
                            $sig->orWhere('created_by', $user->id);
                            if ($user->unidade_administrativa_id) {
                                $sig->orWhere('unidade_atual_id', $user->unidade_administrativa_id);
                            }
                        }
                    });
                }
            }
            $procs = $qProc->where(function ($sub) use ($termo) {
                $sub->where('numero', 'like', "%{$termo}%")
                    ->orWhere('assunto', 'like', "%{$termo}%")
                    ->orWhereHas('interessado', function ($cli) use ($termo) {
                        $cli->where('nome', 'like', "%{$termo}%")
                            ->orWhere('razao_social', 'like', "%{$termo}%")
                            ->orWhere('cpf', 'like', "%{$termo}%")
                            ->orWhere('cnpj', 'like', "%{$termo}%");
                    });
            })->latest('id')->take(5)->get();

            if ($procs->isNotEmpty()) {
                $totalGeral += $procs->count();
                $sections[] = [
                    'key' => 'processos',
                    'title' => 'Processos & Protocolo Digital',
                    'icon' => 'document-text',
                    'badge_color' => 'blue',
                    'items' => $procs->map(function ($p) {
                        return [
                            'id' => $p->id,
                            'title' => $p->numero,
                            'subtitle' => Str::limit($p->assunto, 45) . ($p->interessado ? ' • ' . $p->interessado->nome : ''),
                            'badge' => ucfirst($p->status ?? 'Em Tramitação'),
                            'badge_type' => $p->prioridade === 'urgente' ? 'danger' : 'info',
                            'url' => route('protocolo.show', $p->id),
                        ];
                    })->values(),
                ];
            }
        }

        // 2. Zeladoria Urbana
        if ($perm['zeladoria']) {
            $qZel = OrdemServico::with(['categoriaZeladoria', 'cliente'])
                ->where(function ($z) {
                    $z->whereNotNull('categoria_zeladoria_id')
                      ->orWhere('tipo_os', 'Zeladoria Urbana')
                      ->orWhereNotNull('logradouro');
                });
            if ($perm['isPortalCidadao']) {
                $qZel->where('cliente_id', $perm['clienteId']);
            }
            $zeladorias = $qZel->where(function ($sub) use ($termo) {
                $sub->where('codigo_interno', 'like', "%{$termo}%")
                    ->orWhere('logradouro', 'like', "%{$termo}%")
                    ->orWhere('bairro', 'like', "%{$termo}%")
                    ->orWhere('relato_cliente', 'like', "%{$termo}%");
            })->latest('id')->take(5)->get();

            if ($zeladorias->isNotEmpty()) {
                $totalGeral += $zeladorias->count();
                $sections[] = [
                    'key' => 'zeladoria',
                    'title' => 'Zeladoria & Obras Municipais',
                    'icon' => 'wrench-screwdriver',
                    'badge_color' => 'amber',
                    'items' => $zeladorias->map(function ($z) {
                        return [
                            'id' => $z->id,
                            'title' => $z->codigo_interno ?: 'Demanda #' . $z->id,
                            'subtitle' => ($z->categoriaZeladoria?->nome ?: 'Demanda') . ' • ' . ($z->bairro ?: $z->logradouro ?: 'Sem local'),
                            'badge' => ucfirst($z->status ?? 'Aberto'),
                            'badge_type' => 'warning',
                            'url' => route('zeladoria.show', $z->id),
                        ];
                    })->values(),
                ];
            }
        }

        // 3. Almoxarifado
        if ($perm['almoxarifado']) {
            $reqs = RequisicaoMaterial::with('unidadeSolicitante')
                ->where(function ($sub) use ($termo) {
                    $sub->where('numero', 'like', "%{$termo}%")
                        ->orWhere('justificativa', 'like', "%{$termo}%");
                })->latest('id')->take(4)->get();

            if ($reqs->isNotEmpty()) {
                $totalGeral += $reqs->count();
                $sections[] = [
                    'key' => 'almoxarifado',
                    'title' => 'Requisições de Suprimentos',
                    'icon' => 'cube',
                    'badge_color' => 'emerald',
                    'items' => $reqs->map(function ($r) {
                        return [
                            'id' => $r->id,
                            'title' => $r->numero ?: 'Req. #' . $r->id,
                            'subtitle' => ($r->unidadeSolicitante?->sigla ?: 'Setor') . ' • ' . Str::limit($r->justificativa, 40),
                            'badge' => $r->situacao_formatada ?? ucfirst($r->situacao),
                            'badge_type' => 'success',
                            'url' => route('almoxarifado.requisicoes.show', $r->id),
                        ];
                    })->values(),
                ];
            }
        }

        // 4. Cidadãos / Contribuintes
        if ($perm['cidadaos']) {
            $cids = Cliente::where(function ($sub) use ($termo) {
                $sub->where('nome', 'like', "%{$termo}%")
                    ->orWhere('razao_social', 'like', "%{$termo}%")
                    ->orWhere('cpf', 'like', "%{$termo}%")
                    ->orWhere('cnpj', 'like', "%{$termo}%");
            })->take(4)->get();

            if ($cids->isNotEmpty()) {
                $totalGeral += $cids->count();
                $sections[] = [
                    'key' => 'cidadaos',
                    'title' => 'Cidadãos & Contribuintes',
                    'icon' => 'users',
                    'badge_color' => 'purple',
                    'items' => $cids->map(function ($c) {
                        return [
                            'id' => $c->id,
                            'title' => $c->nome,
                            'subtitle' => 'Doc: ' . ($c->cpf ?: $c->cnpj ?: 'Não informado'),
                            'badge' => 'Cidadão',
                            'badge_type' => 'info',
                            'url' => route('clientes.show', $c->id),
                        ];
                    })->values(),
                ];
            }
        }

        // 5. Secretarias
        if ($perm['secretarias']) {
            $secs = UnidadeAdministrativa::with('responsavel')->where(function ($sub) use ($termo) {
                $sub->where('nome', 'like', "%{$termo}%")
                    ->orWhere('sigla', 'like', "%{$termo}%")
                    ->orWhereHas('responsavel', function ($u) use ($termo) {
                        $u->where('name', 'like', "%{$termo}%");
                    });
            })->take(4)->get();

            if ($secs->isNotEmpty()) {
                $totalGeral += $secs->count();
                $sections[] = [
                    'key' => 'secretarias',
                    'title' => 'Secretarias & Órgãos',
                    'icon' => 'building-library',
                    'badge_color' => 'indigo',
                    'items' => $secs->map(function ($s) {
                        return [
                            'id' => $s->id,
                            'title' => $s->sigla . ' - ' . $s->nome,
                            'subtitle' => 'Titular: ' . ($s->responsavel?->name ?: 'Gabinete'),
                            'badge' => 'Pasta',
                            'badge_type' => 'info',
                            'url' => route('secretarias.show', $s->id),
                        ];
                    })->values(),
                ];
            }
        }

        // 6. Frota Municipal
        if ($perm['frota']) {
            $veics = Veiculo::with('unidadeAdministrativa')
                ->where('ativo', true)
                ->where(function ($sub) use ($termo) {
                    $sub->where('placa', 'like', "%{$termo}%")
                        ->orWhere('modelo', 'like', "%{$termo}%")
                        ->orWhere('marca', 'like', "%{$termo}%")
                        ->orWhere('tipo', 'like', "%{$termo}%")
                        ->orWhere('patrimonio_tombamento', 'like', "%{$termo}%");
                })->take(4)->get();

            if ($veics->isNotEmpty()) {
                $totalGeral += $veics->count();
                $sections[] = [
                    'key' => 'frota',
                    'title' => 'Frota Municipal & Veículos',
                    'icon' => 'truck',
                    'badge_color' => 'blue',
                    'items' => $veics->map(function ($v) {
                        return [
                            'id' => $v->id,
                            'title' => $v->placa . ' • ' . $v->modelo,
                            'subtitle' => ($v->unidadeAdministrativa?->sigla ?: 'Garagem') . ' • ' . ucfirst($v->tipo) . ' • ' . number_format($v->km_atual, 0, ',', '.') . ' km',
                            'badge' => ucfirst(str_replace('_', ' ', $v->status)),
                            'badge_type' => $v->status === 'disponivel' ? 'success' : ($v->status === 'em_viagem' ? 'info' : 'warning'),
                            'url' => route('frota.show', $v->id),
                        ];
                    })->values(),
                ];
            }
        }

        return response()->json([
            'termo' => $termo,
            'total' => $totalGeral,
            'sections' => $sections,
        ]);
    }
}
