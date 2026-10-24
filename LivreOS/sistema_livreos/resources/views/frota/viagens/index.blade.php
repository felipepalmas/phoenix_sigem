{{--
 * Componente da aplicação Phoenix - SIGEM
 * Frota — diário de bordo: saídas e retornos dos veículos (padrão SIGEM Clean)
 *
 * @license AGPL-3.0
 --}}
@extends('layouts.app')

@section('content')
@php
    $painelInicial = old('_painel', '');
    $fmtNum = fn ($n) => number_format((float) $n, 0, ',', '.');
@endphp

<div x-data="{ painel: @js($painelInicial) }">
<x-sigem.page-header title="Diário de bordo" subtitle="Saídas e retornos dos veículos da frota, com motorista, destino e hodômetro">
    <x-slot:actions>
        <x-sigem.button variant="primary" @click="painel = painel === 'saida' ? '' : 'saida'" ::aria-expanded="painel === 'saida'">
            <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Registrar saída
        </x-sigem.button>
    </x-slot:actions>
</x-sigem.page-header>

<x-sigem.flash />

@if($veiculosDisponiveis->isEmpty())
    <div x-show="painel === 'saida'" x-cloak class="sigem-alert sigem-alert-warn mb-4" role="status">
        <span>Nenhum veículo disponível no momento: todos estão em viagem, em manutenção ou inoperantes. Registre o retorno de uma viagem para liberar um veículo.</span>
    </div>
@else
    @include('frota.partials.form-saida', ['veiculosDisponiveis' => $veiculosDisponiveis, 'motoristas' => $motoristas, 'secretarias' => $secretarias])
@endif

<div class="sigem-card">
    <x-sigem.tabs :items="$abas" label="Recorte de viagens" class="px-3" />

    <form method="GET" action="{{ route('frota.viagens.index') }}" class="flex flex-wrap gap-2.5 p-3">
        <input type="hidden" name="recorte" value="{{ $recorte }}">
        <label for="v-busca" class="sr-only">Buscar por número, placa, destino ou motorista</label>
        <input id="v-busca" type="search" name="busca" value="{{ request('busca') }}" placeholder="Número, placa, modelo, destino ou motorista" class="sigem-input min-w-0 flex-[1_1_320px]">
        <x-sigem.button type="submit">Buscar</x-sigem.button>
        @if($filtrosAtivos)
            <x-sigem.button variant="ghost" href="{{ route('frota.viagens.index', ['recorte' => $recorte]) }}">Limpar busca</x-sigem.button>
        @endif
    </form>

    @if($viagens->isEmpty())
        <x-sigem.empty title="Nenhuma viagem neste recorte"
            :description="$filtrosAtivos ? 'Nenhuma viagem corresponde à busca.' : ($recorte === 'em_andamento' ? 'Nenhum veículo está em viagem agora.' : 'As saídas registradas aparecem aqui.')">
            @if(!$filtrosAtivos && $veiculosDisponiveis->isNotEmpty())<x-sigem.button @click="painel = 'saida'; window.scrollTo({ top: 0, behavior: 'smooth' })">Registrar saída</x-sigem.button>@endif
        </x-sigem.empty>
    @else
        <x-sigem.table min-width="900px">
            <x-slot:head>
                <th scope="col">Saída</th>
                <th scope="col">Veículo</th>
                <th scope="col">Motorista</th>
                <th scope="col">Destino e finalidade</th>
                <th scope="col" class="text-right">Percurso</th>
                <th scope="col">Situação</th>
            </x-slot:head>
            @foreach($viagens as $viagem)
                @php
                    $un = $viagem->veiculo?->unidade_medida ?? 'km';
                    $chave = 'retorno-' . $viagem->id;
                    $pendencias = $viagem->pendencias_checklist;
                @endphp
                <tr>
                    <td class="whitespace-nowrap">
                        {{ $viagem->data_saida?->format('d/m/Y H:i') }}
                        <div class="sigem-muted">{{ $viagem->numero }}</div>
                    </td>
                    <td class="whitespace-nowrap">
                        @if($viagem->veiculo)
                            <a href="{{ route('frota.show', $viagem->veiculo) }}" class="sigem-link font-mono tracking-wide">{{ $viagem->veiculo->placa }}</a>
                            <div class="max-w-[180px] truncate sigem-muted">{{ $viagem->veiculo->modelo }}</div>
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        {{ $viagem->motorista?->nome ?? '—' }}
                        @if($viagem->unidadeAdministrativa)<div class="sigem-muted" title="{{ $viagem->unidadeAdministrativa->nome }}">{{ $viagem->unidadeAdministrativa->sigla ?: $viagem->unidadeAdministrativa->nome }}</div>@endif
                    </td>
                    <td class="max-w-[300px]">
                        <div class="truncate">{{ $viagem->destino }}</div>
                        <div class="truncate sigem-muted" title="{{ $viagem->finalidade }}">{{ $viagem->finalidade }}</div>
                        @if($viagem->ocorrencias)<div class="truncate text-xs font-semibold text-sigem-warn dark:text-amber-300" title="{{ $viagem->ocorrencias }}">Ocorrência: {{ $viagem->ocorrencias }}</div>@endif
                        @if($pendencias)<div class="truncate text-xs font-semibold text-sigem-warn dark:text-amber-300">Checklist pendente: {{ implode(', ', $pendencias) }}</div>@endif
                    </td>
                    <td class="whitespace-nowrap text-right tabular-nums">
                        @if($viagem->situacao === 'concluida')
                            {{ $fmtNum($viagem->km_percorrido) }} {{ $un }}
                            <div class="sigem-muted">retorno {{ $viagem->data_retorno?->format('d/m H:i') }}</div>
                        @else
                            <span class="sigem-muted">saiu com {{ $fmtNum($viagem->km_saida) }} {{ $un }}</span>
                        @endif
                    </td>
                    <td class="whitespace-nowrap">
                        <x-sigem.status :tom="$viagem->situacao_tom">{{ $viagem->situacao_formatada }}</x-sigem.status>
                        @if($viagem->situacao === 'em_andamento')
                            <div class="mt-1.5">
                                <x-sigem.button size="sm" @click="painel = painel === '{{ $chave }}' ? '' : '{{ $chave }}'" ::aria-expanded="painel === '{{ $chave }}'" aria-controls="painel-{{ $chave }}">Registrar retorno</x-sigem.button>
                            </div>
                        @endif
                    </td>
                </tr>
                @if($viagem->situacao === 'em_andamento')
                    <tr x-show="painel === '{{ $chave }}'" x-cloak id="painel-{{ $chave }}" class="hover:bg-transparent">
                        <td colspan="6" class="bg-sigem-primary-soft/40 dark:bg-white/[0.03]">
                            <p class="m-0 mb-3 font-semibold">Retorno da viagem {{ $viagem->numero }} — {{ $viagem->veiculo?->placa }}</p>
                            @include('frota.partials.form-retorno', ['viagem' => $viagem])
                        </td>
                    </tr>
                @endif
            @endforeach
        </x-sigem.table>
        <div class="flex flex-wrap items-center gap-2 border-t border-sigem-line px-4 py-3 text-[13px] text-sigem-muted dark:border-gray-800 dark:text-gray-400">
            <span class="flex-1">Mostrando {{ $viagens->firstItem() }}–{{ $viagens->lastItem() }} de {{ $viagens->total() }}</span>
            @if($viagens->hasPages())
                <x-sigem.button size="sm" :href="$viagens->previousPageUrl()" :class="$viagens->onFirstPage() ? 'pointer-events-none opacity-50' : ''">Anterior</x-sigem.button>
                <x-sigem.button size="sm" :href="$viagens->nextPageUrl()" :class="$viagens->hasMorePages() ? '' : 'pointer-events-none opacity-50'">Próxima</x-sigem.button>
            @endif
        </div>
    @endif
</div>
</div>
@endsection
