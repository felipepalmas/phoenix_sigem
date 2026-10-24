{{--
 * Componente da aplicação Phoenix - SIGEM (Zero Papel)
 * Almoxarifado — detalhe da requisição com ação contextual (padrão SIGEM Clean)
 *
 * @license AGPL-3.0
 --}}
@extends('layouts.app')

@section('content')
@php
    use App\Models\RequisicaoMaterial;

    $tons = ['pendente_aprovacao' => 'info', 'aprovada' => 'info', 'atendida_parcial' => 'warn', 'atendida' => 'ok', 'concluida' => 'ok', 'recusada' => 'danger', 'cancelada' => 'neutral'];
    $qtd = fn ($v) => rtrim(rtrim(number_format((float) $v, 4, ',', '.'), '0'), ',');
    $sigla = fn ($u) => $u?->sigla ?: ($u?->nome ?? '—');
    $encerrada = in_array($requisicao->situacao, ['concluida', 'recusada', 'cancelada'], true);
    $mostraSaldo = in_array($requisicao->situacao, ['pendente_aprovacao', 'aprovada', 'atendida_parcial'], true);
    $pendentes = $requisicao->itens->mapWithKeys(fn ($i) => [$i->id => RequisicaoMaterial::pendenteDoItem($i)]);
    $itensComPendencia = $pendentes->filter(fn ($p) => $p > 0)->count();
    $faltaSaldo = $requisicao->itens->filter(fn ($i) => $pendentes[$i->id] > 0 && (float) $i->produto?->estoque_quantidade < $pendentes[$i->id])->count();
@endphp

<div x-data="{ painel: @js(old('_painel', '')) }">
<x-sigem.page-header :title="'Requisição ' . $requisicao->numero"
    :subtitle="'Solicitada por ' . ($requisicao->solicitante?->name ?? 'servidor não informado') . ' em ' . $requisicao->created_at->format('d/m/Y') . ' às ' . $requisicao->created_at->format('H:i')"
    :breadcrumbs="['Requisições' => route('almoxarifado.requisicoes.index'), $requisicao->numero => null]">
    <x-slot:badges>
        <x-sigem.status :tom="$tons[$requisicao->situacao] ?? 'neutral'">{{ $requisicao->situacao_formatada }}</x-sigem.status>
        @if($requisicao->prioridade === 'urgente')<x-sigem.status tom="danger">Urgente</x-sigem.status>@endif
    </x-slot:badges>
    <x-slot:actions>
        @if($podeAprovar)
            <x-sigem.button variant="primary" @click="painel = painel === 'aprovar' ? '' : 'aprovar'" ::aria-expanded="painel === 'aprovar'">Aprovar</x-sigem.button>
            <x-sigem.button variant="danger" @click="painel = painel === 'recusar' ? '' : 'recusar'" ::aria-expanded="painel === 'recusar'">Recusar</x-sigem.button>
        @endif
        @if($podeAtender)
            <x-sigem.button variant="primary" @click="painel = painel === 'atender' ? '' : 'atender'" ::aria-expanded="painel === 'atender'">Atender e baixar estoque</x-sigem.button>
        @endif
        @if($podeConfirmar)
            <x-sigem.button :variant="$podeAtender ? 'secondary' : 'primary'" @click="painel = painel === 'confirmar' ? '' : 'confirmar'" ::aria-expanded="painel === 'confirmar'">Confirmar recebimento</x-sigem.button>
        @endif
    </x-slot:actions>
</x-sigem.page-header>

<x-sigem.flash />

{{-- Quem é o próximo a agir, quando não é o usuário atual --}}
@if($requisicao->situacao === 'pendente_aprovacao' && ! $podeAprovar)
    <div class="sigem-alert sigem-alert-info mb-4" role="status">
        <span>Aguardando aprovação da chefia da <strong>{{ $requisicao->unidadeSolicitante?->nome ?? 'secretaria solicitante' }}</strong>.
            @if((int) $requisicao->solicitante_user_id === (int) auth()->id()) Quem pede não pode aprovar o próprio pedido. @endif</span>
    </div>
@elseif(in_array($requisicao->situacao, ['aprovada', 'atendida_parcial'], true) && ! $podeAtender)
    <div class="sigem-alert sigem-alert-info mb-4" role="status">
        <span>
            @if($requisicao->situacao === 'aprovada')
                Aprovada. Aguardando o <strong>{{ $requisicao->almoxarifado?->nome ?? 'almoxarifado' }}</strong> separar os materiais.
            @else
                Entregue em parte. {{ $itensComPendencia }} {{ $itensComPendencia === 1 ? 'item ainda tem' : 'itens ainda têm' }} quantidade pendente no <strong>{{ $requisicao->almoxarifado?->nome ?? 'almoxarifado' }}</strong>.
            @endif
        </span>
    </div>
@elseif($requisicao->situacao === 'atendida' && ! $podeConfirmar)
    <div class="sigem-alert sigem-alert-info mb-4" role="status">
        <span>Materiais separados. Aguardando a <strong>{{ $requisicao->unidadeSolicitante?->nome ?? 'secretaria solicitante' }}</strong> confirmar o recebimento.</span>
    </div>
@elseif($requisicao->situacao === 'recusada')
    <div class="sigem-alert sigem-alert-danger mb-4" role="status">
        <span>Recusada{{ $requisicao->aprovadoPor ? ' por ' . $requisicao->aprovadoPor->name : '' }}{{ $requisicao->aprovado_em ? ' em ' . $requisicao->aprovado_em->format('d/m/Y') : '' }}.
            @if($requisicao->parecer_aprovacao) Motivo: {{ $requisicao->parecer_aprovacao }} @endif</span>
    </div>
@endif

{{-- Painel: aprovar --}}
@if($podeAprovar)
<form x-show="painel === 'aprovar'" x-cloak method="POST" action="{{ route('almoxarifado.requisicoes.aprovar', $requisicao) }}" class="sigem-card mb-5 border-sigem-primary">
    @csrf
    <input type="hidden" name="_painel" value="aprovar">
    <div class="sigem-card-header"><h2 class="sigem-card-title">Aprovar requisição</h2></div>
    <div class="sigem-card-body flex flex-col gap-4">
        <p class="m-0 text-sigem-muted dark:text-gray-400">Ao aprovar, o pedido segue para o <strong class="text-sigem-ink dark:text-white">{{ $requisicao->almoxarifado?->nome ?? 'almoxarifado' }}</strong> separar os materiais.</p>
        <x-sigem.field label="Parecer" for="parecer" help="Opcional. Fica no histórico e é visto pelo almoxarife.">
            <textarea id="parecer" name="parecer" rows="3" maxlength="500" class="sigem-input" placeholder="Ex.: Aprovado; priorizar a entrega na escola">{{ old('parecer') }}</textarea>
        </x-sigem.field>
        <div class="flex justify-end gap-2">
            <x-sigem.button @click="painel = ''">Cancelar</x-sigem.button>
            <x-sigem.button type="submit" variant="primary">Aprovar requisição</x-sigem.button>
        </div>
    </div>
</form>

{{-- Painel: recusar --}}
<form x-show="painel === 'recusar'" x-cloak method="POST" action="{{ route('almoxarifado.requisicoes.recusar', $requisicao) }}" class="sigem-card mb-5 border-red-300 dark:border-red-900">
    @csrf
    <input type="hidden" name="_painel" value="recusar">
    <div class="sigem-card-header"><h2 class="sigem-card-title">Recusar requisição</h2></div>
    <div class="sigem-card-body flex flex-col gap-4">
        <x-sigem.field label="Motivo da recusa" for="motivo" required help="Mínimo de 5 caracteres. O solicitante verá este motivo.">
            <textarea id="motivo" name="motivo" rows="3" required minlength="5" maxlength="500" class="sigem-input" placeholder="Explique por que o pedido não pode ser atendido">{{ old('motivo') }}</textarea>
        </x-sigem.field>
        <div class="flex justify-end gap-2">
            <x-sigem.button @click="painel = ''">Cancelar</x-sigem.button>
            <x-sigem.button type="submit" variant="danger">Recusar requisição</x-sigem.button>
        </div>
    </div>
</form>
@endif

{{-- Painel: atender (entrega agora, limitada ao pendente) --}}
@if($podeAtender)
<form x-show="painel === 'atender'" x-cloak method="POST" action="{{ route('almoxarifado.requisicoes.atender', $requisicao) }}" class="sigem-card mb-5 border-sigem-primary">
    @csrf
    <input type="hidden" name="_painel" value="atender">
    <div class="sigem-card-header">
        <div class="min-w-0 flex-1">
            <h2 class="sigem-card-title">Atender e baixar do estoque</h2>
            <p class="m-0 mt-0.5 text-[13px] text-sigem-muted dark:text-gray-400">Informe quanto está sendo entregue agora. O que faltar fica pendente para outra entrega.</p>
        </div>
    </div>
    @if($faltaSaldo > 0)
        <div class="sigem-alert sigem-alert-warn mx-5 mt-4" role="status">
            <span>{{ $faltaSaldo }} {{ $faltaSaldo === 1 ? 'item tem' : 'itens têm' }} saldo menor que o pendente. Reduza a quantidade para o saldo disponível; o sistema não deixa o estoque ficar negativo.</span>
        </div>
    @endif
    <x-sigem.table min-width="760px" class="mt-3">
        <x-slot:head>
            <th scope="col">Material</th>
            <th scope="col" class="text-right">Solicitada</th>
            <th scope="col" class="text-right">Já entregue</th>
            <th scope="col" class="text-right">Pendente</th>
            <th scope="col" class="text-right">Saldo em estoque</th>
            <th scope="col" class="w-44">Entregar agora</th>
        </x-slot:head>
        @foreach($requisicao->itens as $item)
            @php
                $pend = $pendentes[$item->id];
                $saldo = (float) $item->produto?->estoque_quantidade;
                $un = $item->produto?->unidade ?: 'UN';
            @endphp
            <tr>
                <td>
                    <div class="font-semibold">{{ $item->produto?->nome ?? 'Material removido' }}</div>
                    @if($item->produto?->codigo_sku)<div class="text-[13px] text-sigem-muted dark:text-gray-400">{{ $item->produto->codigo_sku }}</div>@endif
                </td>
                <td class="whitespace-nowrap text-right">{{ $qtd($item->quantidade_solicitada) }} {{ $un }}</td>
                <td class="whitespace-nowrap text-right">{{ $qtd($item->quantidade_atendida) }} {{ $un }}</td>
                <td class="whitespace-nowrap text-right font-semibold">{{ $qtd($pend) }} {{ $un }}</td>
                <td class="whitespace-nowrap text-right">
                    {{ $qtd($saldo) }} {{ $un }}
                    @if($pend > 0 && $saldo < $pend)
                        <div><x-sigem.status tom="warn">Saldo insuficiente</x-sigem.status></div>
                    @endif
                </td>
                <td>
                    @if($pend > 0)
                        <label for="atender-{{ $item->id }}" class="sr-only">Quantidade de {{ $item->produto?->nome }} entregue agora</label>
                        <div class="flex items-center gap-2">
                            <input id="atender-{{ $item->id }}" name="itens[{{ $item->id }}]" type="number" step="any" min="0" max="{{ $pend }}" inputmode="decimal"
                                value="{{ old('itens.' . $item->id, $pend) }}" class="sigem-input w-28 text-right">
                            <span class="text-[13px] text-sigem-muted dark:text-gray-400">{{ $un }}</span>
                        </div>
                    @else
                        <x-sigem.status tom="ok">Entregue</x-sigem.status>
                    @endif
                </td>
            </tr>
        @endforeach
    </x-sigem.table>
    <div class="sigem-card-body flex flex-col gap-4 border-t border-sigem-line dark:border-gray-800">
        <x-sigem.field label="Observações da entrega" for="observacoes" help="Opcional. Ex.: retirado pelo motorista da secretaria, lote, guia de saída.">
            <textarea id="observacoes" name="observacoes" rows="2" maxlength="500" class="sigem-input">{{ old('observacoes') }}</textarea>
        </x-sigem.field>
        <div class="flex justify-end gap-2">
            <x-sigem.button @click="painel = ''">Cancelar</x-sigem.button>
            <x-sigem.button type="submit" variant="primary">Registrar entrega</x-sigem.button>
        </div>
    </div>
</form>
@endif

{{-- Painel: confirmar recebimento --}}
@if($podeConfirmar)
<form x-show="painel === 'confirmar'" x-cloak method="POST" action="{{ route('almoxarifado.requisicoes.confirmar', $requisicao) }}" class="sigem-card mb-5 border-sigem-primary">
    @csrf
    <input type="hidden" name="_painel" value="confirmar">
    <div class="sigem-card-header"><h2 class="sigem-card-title">Confirmar recebimento</h2></div>
    <div class="sigem-card-body flex flex-col gap-4">
        <p class="m-0">Confirme somente depois de conferir os materiais entregues à {{ $requisicao->unidadeSolicitante?->nome ?? 'secretaria' }}. A confirmação encerra a requisição.</p>
        @if($requisicao->situacao === 'atendida_parcial')
            <div class="sigem-alert sigem-alert-warn" role="status">
                <span>Esta requisição foi entregue só em parte ({{ $itensComPendencia }} {{ $itensComPendencia === 1 ? 'item pendente' : 'itens pendentes' }}). Ao confirmar agora, o que falta <strong>não será mais entregue</strong>; faça nova requisição se precisar.</span>
            </div>
        @endif
        <div class="flex justify-end gap-2">
            <x-sigem.button @click="painel = ''">Cancelar</x-sigem.button>
            <x-sigem.button type="submit" variant="primary">Confirmar recebimento</x-sigem.button>
        </div>
    </div>
</form>
@endif

<div class="flex flex-wrap items-start gap-5">
    <div class="flex min-w-0 flex-[2_1_520px] flex-col gap-5">
        <x-sigem.card title="Pedido" id="t-pedido">
            <p class="m-0 whitespace-pre-line">{{ $requisicao->justificativa }}</p>
        </x-sigem.card>

        <x-sigem.card title="Materiais" id="t-itens" :padded="false">
            <x-slot:actions><span class="text-[13px] text-sigem-muted dark:text-gray-400">{{ $requisicao->itens->count() }} {{ $requisicao->itens->count() === 1 ? 'item' : 'itens' }}</span></x-slot:actions>
            <x-sigem.table min-width="640px">
                <x-slot:head>
                    <th scope="col">Material</th>
                    <th scope="col" class="text-right">Solicitada</th>
                    <th scope="col" class="text-right">Entregue</th>
                    @if($mostraSaldo)<th scope="col" class="text-right">Saldo em estoque</th>@endif
                    <th scope="col">Situação</th>
                </x-slot:head>
                @foreach($requisicao->itens as $item)
                    @php
                        $pend = $pendentes[$item->id];
                        $entregue = (float) $item->quantidade_atendida;
                        $saldo = (float) $item->produto?->estoque_quantidade;
                        $un = $item->produto?->unidade ?: 'UN';
                    @endphp
                    <tr>
                        <td>
                            <div class="font-semibold">{{ $item->produto?->nome ?? 'Material removido' }}</div>
                            @if($item->produto?->codigo_sku)<div class="text-[13px] text-sigem-muted dark:text-gray-400">{{ $item->produto->codigo_sku }}</div>@endif
                        </td>
                        <td class="whitespace-nowrap text-right">{{ $qtd($item->quantidade_solicitada) }} {{ $un }}</td>
                        <td class="whitespace-nowrap text-right">{{ $qtd($entregue) }} {{ $un }}</td>
                        @if($mostraSaldo)
                            <td class="whitespace-nowrap text-right">
                                {{ $qtd($saldo) }} {{ $un }}
                                @if($pend > 0 && $saldo < $pend)<div><x-sigem.status tom="warn">Abaixo do pendente</x-sigem.status></div>@endif
                            </td>
                        @endif
                        <td class="whitespace-nowrap">
                            @if($pend <= 0)
                                <x-sigem.status tom="ok">Entregue</x-sigem.status>
                            @elseif($entregue > 0)
                                <x-sigem.status tom="warn">Faltam {{ $qtd($pend) }} {{ $un }}</x-sigem.status>
                            @elseif($encerrada)
                                <x-sigem.status tom="neutral">Não entregue</x-sigem.status>
                            @else
                                <x-sigem.status tom="info">A entregar</x-sigem.status>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-sigem.table>
        </x-sigem.card>

        <x-sigem.card title="Histórico" id="t-hist">
            @php
                $eventos = collect();
                $eventos->push(['quando' => $requisicao->created_at, 'titulo' => 'Requisição enviada para aprovação', 'quem' => $requisicao->solicitante?->name, 'texto' => null]);
                if ($requisicao->aprovado_em) {
                    $eventos->push([
                        'quando' => $requisicao->aprovado_em,
                        'titulo' => $requisicao->situacao === 'recusada' ? 'Recusada pela chefia' : 'Aprovada pela chefia',
                        'quem' => $requisicao->aprovadoPor?->name,
                        'texto' => $requisicao->parecer_aprovacao,
                    ]);
                }
                if ($entregas->isNotEmpty()) {
                    foreach ($entregas as $n => $grupo) {
                        $primeiro = $grupo->first();
                        $eventos->push([
                            'quando' => $primeiro->created_at,
                            'titulo' => ($entregas->count() > 1 ? ($n + 1) . 'ª entrega' : 'Materiais entregues') . ' pelo almoxarifado',
                            'quem' => $primeiro->user?->name,
                            'texto' => null,
                            'itens' => $grupo->map(fn ($m) => $qtd($m->quantidade) . ' ' . ($m->produto?->unidade ?: 'UN') . ' de ' . ($m->produto?->nome ?? 'material'))->all(),
                        ]);
                    }
                    if ($requisicao->observacoes_atendimento) {
                        $ultima = $eventos->pop();
                        $ultima['texto'] = $requisicao->observacoes_atendimento;
                        $eventos->push($ultima);
                    }
                } elseif ($requisicao->atendido_em) {
                    $eventos->push(['quando' => $requisicao->atendido_em, 'titulo' => 'Atendida pelo almoxarifado', 'quem' => $requisicao->atendidoPor?->name, 'texto' => $requisicao->observacoes_atendimento]);
                }
                if ($requisicao->recebido_em) {
                    $eventos->push(['quando' => $requisicao->recebido_em, 'titulo' => 'Recebimento confirmado', 'quem' => $requisicao->recebidoPor?->name, 'texto' => null]);
                }
                $eventos = $eventos->reverse()->values();
            @endphp
            <x-sigem.timeline>
                @foreach($eventos as $ev)
                    <x-sigem.timeline-item :destaque="$loop->first" :ultimo="$loop->last">
                        <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1">
                            <strong>{{ $ev['titulo'] }}</strong>
                            <span class="text-[13px] text-sigem-muted dark:text-gray-400">{{ $ev['quando']->format('d/m/Y H:i') }}{{ $ev['quem'] ? ' · ' . $ev['quem'] : '' }}</span>
                        </div>
                        @if(!empty($ev['itens']))
                            <ul class="m-0 mt-1 list-disc pl-5 text-[13px]">
                                @foreach($ev['itens'] as $linha)<li>{{ $linha }}</li>@endforeach
                            </ul>
                        @endif
                        @if($ev['texto'])<p class="m-0 mt-1 whitespace-pre-line">{{ $ev['texto'] }}</p>@endif
                    </x-sigem.timeline-item>
                @endforeach
            </x-sigem.timeline>
        </x-sigem.card>
    </div>

    <aside class="flex min-w-0 flex-[1_1_280px] flex-col gap-5">
        <x-sigem.card title="Dados da requisição" id="t-dados">
            <dl class="m-0 flex flex-col gap-3 [&_dd]:m-0 [&_dd]:mt-0.5 [&_dd]:font-semibold [&_dt]:text-xs [&_dt]:text-sigem-muted dark:[&_dt]:text-gray-400">
                <div><dt>Secretaria solicitante</dt><dd>{{ $requisicao->unidadeSolicitante?->sigla ? $requisicao->unidadeSolicitante->sigla . ' — ' : '' }}{{ $requisicao->unidadeSolicitante?->nome ?? '—' }}</dd></div>
                <div>
                    <dt>Almoxarifado</dt>
                    <dd>{{ $requisicao->almoxarifado?->nome ?? '—' }}</dd>
                    @if($requisicao->almoxarifado?->localizacao)<dd class="!font-normal text-[13px] text-sigem-muted dark:text-gray-400">{{ $requisicao->almoxarifado->localizacao }}</dd>@endif
                </div>
                <div><dt>Solicitante</dt><dd>{{ $requisicao->solicitante?->name ?? '—' }}</dd></div>
                <div><dt>Prioridade</dt><dd>{{ $requisicao->prioridade === 'urgente' ? 'Urgente' : 'Normal' }}</dd></div>
                @if($requisicao->processo)
                    <div>
                        <dt>Processo vinculado</dt>
                        <dd><a href="{{ route('protocolo.show', $requisicao->processo) }}" class="sigem-link">{{ $requisicao->processo->numero }}</a></dd>
                        <dd class="!font-normal truncate text-[13px] text-sigem-muted dark:text-gray-400" title="{{ $requisicao->processo->assuntoPara(auth()->user()) }}">{{ $requisicao->processo->assuntoPara(auth()->user()) }}</dd>
                    </div>
                @endif
            </dl>
        </x-sigem.card>

        <x-sigem.card title="Etapas" id="t-etapas">
            @php
                $etapas = [
                    ['Aprovação da chefia', $requisicao->aprovado_em, $requisicao->situacao === 'recusada'],
                    ['Separação no almoxarifado', $requisicao->situacao === 'atendida_parcial' ? null : $requisicao->atendido_em, false],
                    ['Recebimento na secretaria', $requisicao->recebido_em, false],
                ];
            @endphp
            <ol class="m-0 flex list-none flex-col gap-2.5 p-0">
                @foreach($etapas as [$rotulo, $data, $recusa])
                    <li class="flex items-center justify-between gap-3">
                        <span>{{ $rotulo }}</span>
                        @if($recusa)
                            <x-sigem.status tom="danger">Recusada</x-sigem.status>
                        @elseif($data)
                            <span class="text-[13px] text-sigem-ok dark:text-green-400">{{ $data->format('d/m/Y') }}</span>
                        @elseif($loop->index === 1 && $requisicao->situacao === 'atendida_parcial')
                            <x-sigem.status tom="warn">Parcial</x-sigem.status>
                        @elseif($requisicao->situacao === 'recusada' || $requisicao->situacao === 'cancelada')
                            <span class="text-[13px] text-sigem-muted">—</span>
                        @else
                            <span class="text-[13px] text-sigem-muted dark:text-gray-400">pendente</span>
                        @endif
                    </li>
                @endforeach
            </ol>
        </x-sigem.card>
    </aside>
</div>
</div>
@endsection
