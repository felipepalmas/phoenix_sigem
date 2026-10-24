{{--
 * Componente da aplicação Phoenix - SIGEM
 * Zeladoria — lista de demandas com mapa opcional (padrão SIGEM Clean)
 *
 * @license AGPL-3.0
 --}}
@extends('layouts.app')

@section('content')
@php use App\Support\StatusZeladoria; @endphp
<div x-data="{ mapa: false, mapaIniciado: false }">
<x-sigem.page-header title="Zeladoria" subtitle="Demandas de serviços urbanos: iluminação, buracos, poda, limpeza e drenagem">
    <x-slot:actions>
        <x-sigem.button @click="mapa = !mapa; if (mapa && !mapaIniciado) { mapaIniciado = true; $nextTick(() => window.iniciarMapaZeladoria && window.iniciarMapaZeladoria()) }">
            <span x-text="mapa ? 'Ocultar mapa' : 'Ver no mapa'">Ver no mapa</span>
        </x-sigem.button>
        <x-sigem.button variant="primary" href="{{ route('zeladoria.create') }}">
            <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Nova demanda
        </x-sigem.button>
    </x-slot:actions>
</x-sigem.page-header>

<x-sigem.flash />

<div x-show="mapa" x-cloak class="sigem-card mb-5 overflow-hidden">
    <div id="mapaZeladoria" class="h-[360px] w-full" role="region" aria-label="Mapa das demandas listadas"></div>
    <p class="m-0 border-t border-sigem-line px-4 py-2 text-[13px] text-sigem-muted dark:border-gray-800">{{ $pontosMapa->count() }} demanda(s) deste recorte com localização no mapa.</p>
</div>

<div class="sigem-card">
    <x-sigem.tabs :items="$abas" label="Recorte de demandas" class="px-3" />

    <form method="GET" action="{{ route('zeladoria.index') }}" class="flex flex-wrap gap-2.5 p-3" x-data x-ref="filtros">
        <input type="hidden" name="recorte" value="{{ $recorte }}">
        <label for="z-busca" class="sr-only">Buscar por código, endereço ou cidadão</label>
        <input id="z-busca" type="search" name="busca" value="{{ request('busca') }}" placeholder="Código, rua, relato ou cidadão" class="sigem-input min-w-0 flex-[1_1_260px]">
        <label for="z-cat" class="sr-only">Categoria</label>
        <select id="z-cat" name="categoria_id" class="sigem-input flex-[0_1_240px]" @change="$refs.filtros.submit()">
            <option value="">Qualquer categoria</option>
            @foreach($categorias as $cat)
                <option value="{{ $cat->id }}" @selected((string) request('categoria_id') === (string) $cat->id)>{{ $cat->nome }}</option>
            @endforeach
        </select>
        <label for="z-bairro" class="sr-only">Bairro</label>
        <input id="z-bairro" type="text" name="bairro" value="{{ request('bairro') }}" placeholder="Bairro" class="sigem-input flex-[0_1_180px]">
        <x-sigem.button type="submit">Buscar</x-sigem.button>
        @if($filtrosAtivos)
            <x-sigem.button variant="ghost" href="{{ route('zeladoria.index', ['recorte' => $recorte]) }}">Limpar filtros</x-sigem.button>
        @endif
    </form>

    @if($ordens->isEmpty())
        <x-sigem.empty title="Nenhuma demanda neste recorte" description="Demandas abertas pelo balcão, pelo 156 ou pelo Portal do Cidadão aparecem aqui." />
    @else
        <x-sigem.table min-width="820px">
            <x-slot:head>
                <th scope="col">Código</th>
                <th scope="col">Serviço e local</th>
                <th scope="col">Secretaria</th>
                <th scope="col">Aberta</th>
                <th scope="col">Situação</th>
            </x-slot:head>
            @foreach($ordens as $os)
                @php
                    $prio = mb_strtolower((string) $os->prioridade);
                    $abertura = $os->data_abertura ? \Carbon\Carbon::parse($os->data_abertura) : $os->created_at;
                    $atrasada = !StatusZeladoria::encerrada($os->status) && $os->prevista_conclusao && \Carbon\Carbon::parse($os->prevista_conclusao)->isPast();
                @endphp
                <tr>
                    <td class="whitespace-nowrap">
                        <a href="{{ route('zeladoria.show', $os) }}" class="sigem-link">{{ $os->codigo_interno ?: '#' . $os->id }}</a>
                        @if(in_array($prio, ['urgente', 'emergencial', 'alta'], true))
                            <div class="text-xs font-semibold text-sigem-danger dark:text-red-400">{{ $prio === 'emergencial' ? 'Emergencial' : 'Urgente' }}</div>
                        @endif
                    </td>
                    <td class="max-w-[440px]">
                        <div class="truncate">{{ $os->categoriaZeladoria->nome ?? 'Zeladoria' }} — {{ trim(($os->logradouro ?? '') . ($os->numero_logradouro ? ', ' . $os->numero_logradouro : '')) ?: 'local não informado' }}</div>
                        <div class="truncate text-[13px] text-sigem-muted dark:text-gray-400" title="{{ $os->relato_cliente }}">{{ $os->bairro ? $os->bairro . ' · ' : '' }}{{ \Illuminate\Support\Str::limit($os->relato_cliente, 90) }}</div>
                    </td>
                    <td title="{{ $os->secretariaResponsavel?->nome }}">{{ $os->secretariaResponsavel?->sigla ?: ($os->secretariaResponsavel?->nome ?? '—') }}</td>
                    <td class="whitespace-nowrap">
                        {{ $abertura?->format('d/m/Y') }}
                        @if($atrasada)<div class="text-xs font-semibold text-sigem-danger dark:text-red-400">Prazo vencido</div>@endif
                    </td>
                    <td><x-sigem.status :tom="StatusZeladoria::tom($os->status)">{{ StatusZeladoria::rotulo($os->status) }}</x-sigem.status></td>
                </tr>
            @endforeach
        </x-sigem.table>
        <div class="flex flex-wrap items-center gap-2 border-t border-sigem-line px-4 py-3 text-[13px] text-sigem-muted dark:border-gray-800 dark:text-gray-400">
            <span class="flex-1">Mostrando {{ $ordens->firstItem() }}–{{ $ordens->lastItem() }} de {{ $ordens->total() }}</span>
            @if($ordens->hasPages())
                <x-sigem.button size="sm" :href="$ordens->previousPageUrl()" :class="$ordens->onFirstPage() ? 'pointer-events-none opacity-50' : ''">Anterior</x-sigem.button>
                <x-sigem.button size="sm" :href="$ordens->nextPageUrl()" :class="$ordens->hasMorePages() ? '' : 'pointer-events-none opacity-50'">Próxima</x-sigem.button>
            @endif
        </div>
    @endif
</div>
</div>
@endsection

@push('scripts')
<script>
window.iniciarMapaZeladoria = function () {
    if (!window.sigemMapa) return;
    var pontos = @json($pontosMapa);
    var mapa = window.sigemMapa('mapaZeladoria');
    if (!mapa) return;
    var limites = [];
    pontos.forEach(function (p) {
        limites.push([p.lat, p.lng]);
        L.marker([p.lat, p.lng]).addTo(mapa).bindPopup(
            '<strong>' + sigemEsc(p.codigo) + '</strong><br>' + sigemEsc(p.categoria) + '<br><small>' + sigemEsc(p.local) + ' · ' + sigemEsc(p.situacao) + '</small><br><a href="' + sigemEsc(p.url) + '">Abrir demanda</a>'
        );
    });
    if (limites.length) mapa.fitBounds(limites, { padding: [30, 30], maxZoom: 16 });
    setTimeout(function () { mapa.invalidateSize(); }, 50);
};
</script>
@endpush
