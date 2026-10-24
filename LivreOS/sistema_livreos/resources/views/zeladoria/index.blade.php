{{--
 * Componente da aplicação Phoenix - SIGEM
 * Módulo B: Zeladoria Urbana e Serviços Públicos Municipais
 * Painel de Ocorrências e Mapa de Demandas
 *
 * @license AGPL-3.0
 --}}

@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
            <span>Serviços Urbanos</span>
            <span>/</span>
            <span class="font-semibold text-gray-700 dark:text-gray-300">Zeladoria Municipal</span>
        </div>
        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Painel de Zeladoria Urbana & Obras</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">Acompanhamento georreferenciado de demandas de campo, iluminação, asfalto e limpeza</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('ordens-servico.index') }}" class="rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
            Ver Todas as OS
        </a>
        <a href="{{ route('zeladoria.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 transition">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nova Demanda de Zeladoria
        </a>
    </div>
</div>

@if(session('success'))
<div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-sm font-medium text-green-800 dark:border-green-800 dark:bg-green-900/30 dark:text-green-300">
    {{ session('success') }}
</div>
@endif

<!-- Cards Operacionais -->
<div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Demandas Abertas / Triagem</p>
                <h3 class="mt-1 text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $totalAbertas }}</h3>
            </div>
            <div class="rounded-lg bg-blue-50 p-3 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Equipes em Campo</p>
                <h3 class="mt-1 text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $totalExecucao }}</h3>
            </div>
            <div class="rounded-lg bg-purple-50 p-3 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Demandas Urgentes</p>
                <h3 class="mt-1 text-2xl font-bold text-rose-600 dark:text-rose-400">{{ $totalUrgentes }}</h3>
            </div>
            <div class="rounded-lg bg-rose-50 p-3 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Serviços Concluídos</p>
                <h3 class="mt-1 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $totalConcluidas }}</h3>
            </div>
            <div class="rounded-lg bg-emerald-50 p-3 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>
</div>

<!-- Mapa de Ocorrências de Zeladoria -->
<div class="mb-6 rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
        <div>
            <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="h-5 w-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Mapa Geral de Ocorrências do Município
            </h2>
            <p class="text-xs text-gray-500">Visualização georreferenciada de pontos com demandas de campo</p>
        </div>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
            <span class="h-2 w-2 rounded-full bg-blue-500 animate-pulse"></span>
            {{ count($pontosMapa) }} ocorrências mapeadas
        </span>
    </div>

    <!-- Container do Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <div id="mapaZeladoria" class="h-80 w-full rounded-lg border border-gray-200 dark:border-gray-700 z-10"></div>
</div>

<!-- Filtros de Busca -->
<div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
    <form method="GET" action="{{ route('zeladoria.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <div class="lg:col-span-2">
            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">Buscar por código, logradouro ou cidadão</label>
            <input type="text" name="busca" value="{{ request('busca') }}" placeholder="Ex: ZEL-2026/00001, Rua das Flores, Centro..." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">Categoria de Zeladoria</label>
            <select name="categoria_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                <option value="">Todas as Categorias</option>
                @foreach($categorias as $cat)
                <option value="{{ $cat->id }}" {{ request('categoria_id') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->nome }}
                </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">Bairro</label>
            <input type="text" name="bairro" value="{{ request('bairro') }}" placeholder="Ex: Centro, Jardim..." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="w-full rounded-lg bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-900 dark:bg-gray-700 dark:hover:bg-gray-600 transition">Filtrar</button>
            @if(request()->hasAny(['busca', 'categoria_id', 'status', 'bairro']))
            <a href="{{ route('zeladoria.index') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300" title="Limpar">✕</a>
            @endif
        </div>
    </form>
</div>

<!-- Tabela de Ocorrências -->
<div class="rounded-xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-800 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                <tr>
                    <th class="px-5 py-3.5">Código / Data</th>
                    <th class="px-5 py-3.5">Serviço / Categoria</th>
                    <th class="px-5 py-3.5">Localização / Bairro</th>
                    <th class="px-5 py-3.5">Cidadão Solicitante</th>
                    <th class="px-5 py-3.5 text-center">Prioridade</th>
                    <th class="px-5 py-3.5 text-center">Situação</th>
                    <th class="px-5 py-3.5 text-right">Ação</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                @forelse($ordens as $os)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                    <td class="px-5 py-3.5 font-bold text-brand-600 dark:text-brand-400">
                        <a href="{{ route('zeladoria.show', $os) }}" class="hover:underline">
                            {{ $os->codigo_interno ?: '#' . $os->id }}
                        </a>
                        <div class="text-xs text-gray-400 font-normal mt-0.5">{{ $os->created_at ? $os->created_at->format('d/m/Y H:i') : '—' }}</div>
                    </td>
                    <td class="px-5 py-3.5">
                        @if($os->categoriaZeladoria)
                            <span class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-medium {{ $os->categoriaZeladoria->badge_color }}">
                                {{ $os->categoriaZeladoria->nome }}
                            </span>
                        @else
                            <span class="text-gray-600 dark:text-gray-300">{{ $os->tipo_os ?: 'Zeladoria' }}</span>
                        @endif
                        <div class="text-xs text-gray-500 mt-1 line-clamp-1" title="{{ $os->relato_cliente }}">{{ $os->relato_cliente }}</div>
                    </td>
                    <td class="px-5 py-3.5 text-gray-700 dark:text-gray-300">
                        <div class="font-medium truncate max-w-xs" title="{{ $os->endereco_completo }}">
                            {{ $os->logradouro ?: 'Sem rua' }}{{ $os->numero_logradouro ? ', ' . $os->numero_logradouro : '' }}
                        </div>
                        <div class="text-xs text-gray-400">{{ $os->bairro ?: 'Bairro não informado' }}</div>
                    </td>
                    <td class="px-5 py-3.5 text-gray-600 dark:text-gray-300">
                        {{ $os->cliente ? $os->cliente->nome : 'Munícipe Anônimo' }}
                    </td>
                    <td class="px-5 py-3.5 text-center">
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold
                            {{ in_array($os->prioridade, ['urgente', 'emergencial']) ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' }}">
                            {{ ucfirst($os->prioridade) }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-center">
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold
                            {{ str_contains($os->status, 'Concluída') ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300' : (str_contains($os->status, 'Execução') ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300') }}">
                            {{ $os->status }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="{{ route('zeladoria.show', $os) }}" class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                            Acessar
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                        Nenhuma demanda de zeladoria registrada no momento.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($ordens->hasPages())
    <div class="border-t border-gray-200 px-5 py-3 dark:border-gray-700">
        {{ $ordens->links() }}
    </div>
    @endif
</div>

<!-- Script Leaflet.js para Renderizar os Pontos no Mapa Geral -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var pontos = @json($pontosMapa);
    
    // Coordenadas padrão (Nonoai-RS ou primeiro ponto)
    var defaultLat = pontos.length > 0 ? pontos[0].lat : -27.3600;
    var defaultLng = pontos.length > 0 ? pontos[0].lng : -52.7700;
    var zoom = pontos.length > 0 ? 13 : 13;

    var map = L.map('mapaZeladoria').setView([defaultLat, defaultLng], zoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    function esc(str) {
        if (str === null || str === undefined) return '';
        var div = document.createElement('div');
        div.textContent = String(str);
        return div.innerHTML;
    }

    if (pontos.length > 0) {
        var bounds = [];
        pontos.forEach(function (p) {
            var marker = L.marker([p.lat, p.lng]).addTo(map);
            var safeCodigo = esc(p.codigo);
            var safeCategoria = esc(p.categoria);
            var safeLogradouro = esc(p.logradouro);
            var safeUrl = encodeURI(p.url || '#');

            var popupContent = '<b>' + safeCodigo + '</b><br>' +
                               '<span>' + safeCategoria + '</span><br>' +
                               '<small>' + safeLogradouro + '</small><br>' +
                               '<a href="' + safeUrl + '" class="text-blue-600 underline font-bold text-xs" style="margin-top:4px;display:inline-block;">Acessar Ocorrência</a>';
            marker.bindPopup(popupContent);
            bounds.push([p.lat, p.lng]);
        });
        if (bounds.length > 1) {
            map.fitBounds(bounds, { padding: [30, 30] });
        }
    }
});
</script>
@endsection
