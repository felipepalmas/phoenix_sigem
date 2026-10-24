{{--
 * Componente da aplicação Phoenix - SIGEM (Zero Papel)
 * Almoxarifado — lista de requisições de materiais (padrão SIGEM Clean)
 *
 * @license AGPL-3.0
 --}}
@extends('layouts.app')

@section('content')
@php
    $tons = ['pendente_aprovacao' => 'info', 'aprovada' => 'info', 'atendida_parcial' => 'warn', 'atendida' => 'ok', 'concluida' => 'ok', 'recusada' => 'danger', 'cancelada' => 'neutral'];
    $situacoes = ['pendente_aprovacao' => 'Aguardando aprovação', 'aprovada' => 'Aprovada', 'atendida_parcial' => 'Atendida parcialmente', 'atendida' => 'Atendida', 'concluida' => 'Recebimento confirmado', 'recusada' => 'Recusada', 'cancelada' => 'Cancelada'];
@endphp
<x-sigem.page-header title="Requisições de materiais" subtitle="Pedidos das secretarias ao almoxarifado: aprovação da chefia, separação e entrega">
    <x-slot:actions>
        <x-sigem.button variant="primary" href="{{ route('almoxarifado.requisicoes.create') }}">
            <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Nova requisição
        </x-sigem.button>
    </x-slot:actions>
</x-sigem.page-header>

<x-sigem.flash />

@if($materiaisCriticos > 0)
    <div class="sigem-alert sigem-alert-warn mb-4" role="status">
        <span>
            {{ $materiaisCriticos }} {{ $materiaisCriticos === 1 ? 'material está' : 'materiais estão' }} no estoque mínimo ou abaixo dele.
            <a href="{{ route('almoxarifado.movimentacoes.index') }}" class="sigem-link">Ver Kardex</a>
        </span>
    </div>
@endif

<div class="sigem-card">
    <x-sigem.tabs :items="$abas" label="Recorte de requisições" class="px-3" />

    <form method="GET" action="{{ route('almoxarifado.requisicoes.index') }}" class="flex flex-wrap gap-2.5 p-3" x-data x-ref="filtros">
        <input type="hidden" name="recorte" value="{{ $recorte }}">
        <label for="f-busca" class="sr-only">Buscar por número, justificativa ou solicitante</label>
        <input id="f-busca" type="search" name="busca" value="{{ request('busca') }}" placeholder="Número, justificativa ou solicitante" class="sigem-input min-w-0 flex-[1_1_260px]">
        <label for="f-situacao" class="sr-only">Situação</label>
        <select id="f-situacao" name="situacao" class="sigem-input flex-[0_1_200px]" @change="$refs.filtros.submit()">
            <option value="">Qualquer situação</option>
            @foreach($situacoes as $valor => $rotulo)
                <option value="{{ $valor }}" @selected(request('situacao') === $valor)>{{ $rotulo }}</option>
            @endforeach
        </select>
        <label for="f-unidade" class="sr-only">Secretaria solicitante</label>
        <select id="f-unidade" name="unidade_id" class="sigem-input flex-[0_1_210px]" @change="$refs.filtros.submit()">
            <option value="">Qualquer secretaria</option>
            @foreach($unidades as $u)
                <option value="{{ $u->id }}" @selected((string) request('unidade_id') === (string) $u->id)>{{ $u->sigla ? $u->sigla . ' — ' : '' }}{{ $u->nome }}</option>
            @endforeach
        </select>
        <label for="f-almox" class="sr-only">Almoxarifado</label>
        <select id="f-almox" name="almoxarifado_id" class="sigem-input flex-[0_1_210px]" @change="$refs.filtros.submit()">
            <option value="">Qualquer almoxarifado</option>
            @foreach($almoxarifados as $a)
                <option value="{{ $a->id }}" @selected((string) request('almoxarifado_id') === (string) $a->id)>{{ $a->nome }}</option>
            @endforeach
        </select>
        <x-sigem.button type="submit">Buscar</x-sigem.button>
        @if($filtrosAtivos)
            <x-sigem.button variant="ghost" href="{{ route('almoxarifado.requisicoes.index', ['recorte' => $recorte]) }}">Limpar filtros</x-sigem.button>
        @endif
    </form>

    @if($requisicoes->isEmpty())
        <x-sigem.empty title="Nenhuma requisição neste recorte"
            :description="$filtrosAtivos ? 'Nenhuma requisição corresponde aos filtros. Tente limpar a busca.' : 'Quando uma secretaria pedir materiais ao almoxarifado, o pedido aparece nesta lista.'">
            @unless($filtrosAtivos)<x-sigem.button variant="primary" href="{{ route('almoxarifado.requisicoes.create') }}">Nova requisição</x-sigem.button>@endunless
        </x-sigem.empty>
    @else
        <x-sigem.table min-width="860px">
            <x-slot:head>
                <th scope="col">Número</th>
                <th scope="col">Pedido</th>
                <th scope="col">Secretaria</th>
                <th scope="col">Almoxarifado</th>
                <th scope="col">Data</th>
                <th scope="col">Situação</th>
            </x-slot:head>
            @foreach($requisicoes as $req)
                <tr>
                    <td class="whitespace-nowrap">
                        <a href="{{ route('almoxarifado.requisicoes.show', $req) }}" class="sigem-link">{{ $req->numero }}</a>
                        @if($req->prioridade === 'urgente')
                            <div class="text-xs font-semibold text-sigem-danger dark:text-red-400">Urgente</div>
                        @endif
                    </td>
                    <td>
                        <div class="max-w-[280px] truncate" title="{{ $req->justificativa }}">{{ $req->justificativa }}</div>
                        <div class="max-w-[280px] truncate text-[13px] text-sigem-muted dark:text-gray-400">
                            {{ $req->itens_count }} {{ $req->itens_count === 1 ? 'item' : 'itens' }} · {{ $req->solicitante?->name ?? 'Solicitante não informado' }}
                        </div>
                    </td>
                    <td>
                        <span title="{{ $req->unidadeSolicitante?->nome }}">{{ $req->unidadeSolicitante?->sigla ?: ($req->unidadeSolicitante?->nome ?? '—') }}</span>
                    </td>
                    <td>
                        <div class="max-w-[170px] truncate" title="{{ $req->almoxarifado?->nome }}">{{ $req->almoxarifado?->nome ?? '—' }}</div>
                    </td>
                    <td class="whitespace-nowrap">{{ $req->created_at->format('d/m/Y') }}</td>
                    <td><x-sigem.status :tom="$tons[$req->situacao] ?? 'neutral'">{{ $req->situacao_formatada }}</x-sigem.status></td>
                </tr>
            @endforeach
        </x-sigem.table>
        <div class="flex flex-wrap items-center gap-2 border-t border-sigem-line px-4 py-3 text-[13px] text-sigem-muted dark:border-gray-800 dark:text-gray-400">
            <span class="flex-1">Mostrando {{ $requisicoes->firstItem() }}–{{ $requisicoes->lastItem() }} de {{ $requisicoes->total() }} {{ $requisicoes->total() === 1 ? 'requisição' : 'requisições' }}</span>
            @if($requisicoes->hasPages())
                <x-sigem.button size="sm" :href="$requisicoes->previousPageUrl()" :class="$requisicoes->onFirstPage() ? 'pointer-events-none opacity-50' : ''">Anterior</x-sigem.button>
                <x-sigem.button size="sm" :href="$requisicoes->nextPageUrl()" :class="$requisicoes->hasMorePages() ? '' : 'pointer-events-none opacity-50'">Próxima</x-sigem.button>
            @endif
        </div>
    @endif
</div>
@endsection
