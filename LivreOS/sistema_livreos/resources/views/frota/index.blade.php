{{--
 * Componente da aplicação Phoenix - SIGEM
 * Frota — lista de veículos e máquinas (padrão SIGEM Clean)
 *
 * @license AGPL-3.0
 --}}
@extends('layouts.app')

@section('content')
@php use App\Models\Veiculo; @endphp
<x-sigem.page-header title="Frota" subtitle="Veículos e máquinas da Prefeitura: situação, lotação e hodômetro">
    @if($podeGerenciar)
        <x-slot:actions>
            <x-sigem.button variant="primary" href="{{ route('frota.create') }}">
                <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                Cadastrar veículo
            </x-sigem.button>
        </x-slot:actions>
    @endif
</x-sigem.page-header>

<x-sigem.flash />

@include('frota.partials.alerta-cnh', ['alertasCnh' => $alertasCnh, 'linkMotoristas' => true])

<div class="sigem-card">
    <x-sigem.tabs :items="$abas" label="Recorte de veículos" class="px-3" />

    <form method="GET" action="{{ route('frota.index') }}" class="flex flex-wrap gap-2.5 p-3" x-data x-ref="filtros">
        <input type="hidden" name="recorte" value="{{ $recorte }}">
        <label for="f-busca" class="sr-only">Buscar por placa, modelo, marca ou patrimônio</label>
        <input id="f-busca" type="search" name="busca" value="{{ request('busca') }}" placeholder="Placa, modelo, marca ou patrimônio" class="sigem-input min-w-0 flex-[1_1_260px]">
        <label for="f-tipo" class="sr-only">Tipo</label>
        <select id="f-tipo" name="tipo" class="sigem-input flex-[0_1_220px]" @change="$refs.filtros.submit()">
            <option value="">Qualquer tipo</option>
            @foreach(Veiculo::TIPOS as $valor => $rotulo)
                <option value="{{ $valor }}" @selected(request('tipo') === $valor)>{{ $rotulo }}</option>
            @endforeach
        </select>
        <label for="f-secretaria" class="sr-only">Secretaria</label>
        <select id="f-secretaria" name="secretaria_id" class="sigem-input flex-[0_1_220px]" @change="$refs.filtros.submit()">
            <option value="">Qualquer secretaria</option>
            @foreach($secretarias as $s)
                <option value="{{ $s->id }}" @selected((string) request('secretaria_id') === (string) $s->id)>{{ $s->sigla ? $s->sigla . ' — ' : '' }}{{ $s->nome }}</option>
            @endforeach
        </select>
        <x-sigem.button type="submit">Buscar</x-sigem.button>
        @if($filtrosAtivos)
            <x-sigem.button variant="ghost" href="{{ route('frota.index', ['recorte' => $recorte]) }}">Limpar filtros</x-sigem.button>
        @endif
    </form>

    @if($veiculos->isEmpty())
        <x-sigem.empty title="Nenhum veículo neste recorte"
            :description="$filtrosAtivos ? 'Nenhum veículo corresponde aos filtros. Tente limpar a busca.' : 'Os veículos e máquinas cadastrados na frota aparecem aqui.'">
            @if(!$filtrosAtivos && $podeGerenciar)<x-sigem.button variant="primary" href="{{ route('frota.create') }}">Cadastrar veículo</x-sigem.button>@endif
        </x-sigem.empty>
    @else
        <x-sigem.table min-width="820px">
            <x-slot:head>
                <th scope="col">Placa</th>
                <th scope="col">Veículo</th>
                <th scope="col">Secretaria</th>
                <th scope="col" class="text-right">Hodômetro</th>
                <th scope="col">Situação</th>
            </x-slot:head>
            @foreach($veiculos as $v)
                <tr>
                    <td class="whitespace-nowrap">
                        <a href="{{ route('frota.show', $v) }}" class="sigem-link font-mono tracking-wide">{{ $v->placa }}</a>
                        @if($v->patrimonio_tombamento)<div class="sigem-muted">Pat. {{ $v->patrimonio_tombamento }}</div>@endif
                    </td>
                    <td class="max-w-[380px]">
                        <div class="truncate">{{ $v->marca }} {{ $v->modelo }} <span class="text-sigem-muted dark:text-gray-400">· {{ $v->ano_fabricacao }}/{{ $v->ano_modelo }}</span></div>
                        <div class="truncate sigem-muted">{{ $v->tipo_formatado }} · {{ $v->combustivel_formatado }}</div>
                    </td>
                    <td title="{{ $v->unidadeAdministrativa?->nome }}">{{ $v->unidadeAdministrativa?->sigla ?: ($v->unidadeAdministrativa?->nome ?? 'Garagem central') }}</td>
                    <td class="whitespace-nowrap text-right tabular-nums">
                        {{ $v->hodometro_formatado }}
                        @if($v->usa_horimetro)<div class="sigem-muted">horímetro</div>@endif
                    </td>
                    <td><x-sigem.status :tom="$v->status_tom">{{ $v->status_formatado }}</x-sigem.status></td>
                </tr>
            @endforeach
        </x-sigem.table>
        <div class="flex flex-wrap items-center gap-2 border-t border-sigem-line px-4 py-3 text-[13px] text-sigem-muted dark:border-gray-800 dark:text-gray-400">
            <span class="flex-1">Mostrando {{ $veiculos->firstItem() }}–{{ $veiculos->lastItem() }} de {{ $veiculos->total() }}</span>
            @if($veiculos->hasPages())
                <x-sigem.button size="sm" :href="$veiculos->previousPageUrl()" :class="$veiculos->onFirstPage() ? 'pointer-events-none opacity-50' : ''">Anterior</x-sigem.button>
                <x-sigem.button size="sm" :href="$veiculos->nextPageUrl()" :class="$veiculos->hasMorePages() ? '' : 'pointer-events-none opacity-50'">Próxima</x-sigem.button>
            @endif
        </div>
    @endif
</div>
@endsection
