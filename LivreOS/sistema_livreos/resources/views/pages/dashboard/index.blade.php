{{--
 * Componente da aplicação Phoenix - SIGEM
 * Centro de Comando e Gestão Municipal Integrada
 *
 * @license AGPL-3.0
 --}}

@extends('layouts.app')

@section('content')
<!-- Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<style>
    /* Suporte Completo ao Tema Escuro para o Centro de Comando e Mapa Leaflet */
    .dark #mapaCentroComando {
        background-color: #111827 !important;
    }
    .dark #mapaCentroComando .leaflet-tile-pane {
        filter: brightness(0.6) invert(1) contrast(3) hue-rotate(200deg) saturate(0.3) brightness(0.75);
    }
    .dark #mapaCentroComando .leaflet-control-zoom a {
        background-color: #1f2937 !important;
        color: #f3f4f6 !important;
        border-color: #374151 !important;
    }
    .dark #mapaCentroComando .leaflet-control-zoom a:hover {
        background-color: #374151 !important;
    }
    .dark #mapaCentroComando .leaflet-control-attribution {
        background-color: rgba(17, 24, 39, 0.85) !important;
        color: #9ca3af !important;
    }
    .dark #mapaCentroComando .leaflet-control-attribution a {
        color: #60a5fa !important;
    }
    .dark .leaflet-popup-content-wrapper,
    .dark .leaflet-popup-tip {
        background-color: #1f2937 !important;
        color: #f3f4f6 !important;
        border: 1px solid #374151 !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5) !important;
    }
    .dark .leaflet-popup-content {
        color: #f3f4f6 !important;
    }
    .dark .popup-desc {
        color: #9ca3af !important;
    }
    .dark .popup-badge {
        background-color: #374151 !important;
        color: #e5e7eb !important;
    }
    .dark .leaflet-container a.leaflet-popup-close-button {
        color: #9ca3af !important;
    }
</style>

<div class="dashboard-page space-y-6">

    <!-- Header do Centro de Comando Municipal -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="flex items-center gap-2.5 mb-1.5">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                        <span class="h-2 w-2 rounded-full bg-emerald-600 animate-pulse"></span>
                        CENTRO DE COMANDO OPERACIONAL — ATIVO
                    </span>
                    <span class="text-xs text-gray-400 dark:text-gray-500">•</span>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                        {{ now()->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                    </span>
                </div>
                <h1 class="text-2xl font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-3xl">
                    Painel de Governança & Gestão Municipal
                </h1>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                    Visão integrada em tempo real de Processos Zero Papel, Zeladoria Urbana, Suprimentos e Atendimento aos Cidadãos.
                </p>
            </div>

            <!-- Ações Rápidas Executivas (Quick Actions) -->
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('protocolo.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-brand-700 transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Autuar Processo Digital
                </a>
                <a href="{{ route('zeladoria.create') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-xs font-bold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition">
                    <svg class="h-4 w-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Demanda de Zeladoria
                </a>
                <a href="{{ route('almoxarifado.requisicoes.create') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-xs font-bold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition">
                    <svg class="h-4 w-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    Requisitar Material
                </a>
                <a href="{{ route('clientes.create') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-xs font-bold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition">
                    <svg class="h-4 w-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    Novo Cidadão
                </a>
            </div>
        </div>
    </div>

    <!-- Linha 1: Cards Estratégicos Executivos (KPIs Municipais) -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        
        <!-- CARD 1: PROTOCOLO DIGITAL / ZERO PAPEL -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800 hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Zero Papel</span>
                    <h3 class="mt-1 text-3xl font-extrabold text-gray-900 dark:text-white">
                        {{ $kpiProcessos['tramitando'] }}
                    </h3>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Processos em Tramitação</p>
                </div>
                <div class="rounded-xl bg-blue-50 p-3 text-blue-600 dark:bg-blue-900/30 dark:text-blue-300">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3 text-xs dark:border-gray-700">
                <span class="inline-flex items-center gap-1 font-semibold text-rose-600 dark:text-rose-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-rose-600 animate-pulse"></span>
                    {{ $kpiProcessos['urgentes'] }} Urgentes
                </span>
                <span class="font-medium text-gray-500 dark:text-gray-400">
                    {{ $kpiProcessos['concluidos'] }} Concluídos
                </span>
                <a href="{{ route('protocolo.index') }}" class="font-bold text-brand-600 hover:underline dark:text-brand-400">Abrir →</a>
            </div>
        </div>

        <!-- CARD 2: ZELADORIA URBANA & OCORRÊNCIAS -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800 hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Zeladoria & Obras</span>
                    <h3 class="mt-1 text-3xl font-extrabold text-gray-900 dark:text-white">
                        {{ $kpiZeladoria['abertas'] }}
                    </h3>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Demandas Abertas / Triagem</p>
                </div>
                <div class="rounded-xl bg-amber-50 p-3 text-amber-600 dark:bg-amber-900/30 dark:text-amber-300">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3 text-xs dark:border-gray-700">
                <span class="font-semibold text-blue-600 dark:text-blue-400">
                    {{ $kpiZeladoria['em_campo'] }} Em Campo
                </span>
                <span class="font-medium text-emerald-600 dark:text-emerald-400">
                    {{ $kpiZeladoria['concluidas'] }} Concluídas
                </span>
                <a href="{{ route('zeladoria.index') }}" class="font-bold text-brand-600 hover:underline dark:text-brand-400">Abrir →</a>
            </div>
        </div>

        <!-- CARD 3: ALMOXARIFADO & SUPRIMENTOS -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800 hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Almoxarifado</span>
                    <h3 class="mt-1 text-3xl font-extrabold text-gray-900 dark:text-white">
                        {{ $kpiAlmoxarifado['pendente_aprovacao'] }}
                    </h3>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Aguardando Chefia</p>
                </div>
                <div class="rounded-xl bg-emerald-50 p-3 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-300">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3 text-xs dark:border-gray-700">
                <span class="font-medium text-gray-500 dark:text-gray-400">
                    {{ $kpiAlmoxarifado['aprovadas'] }} No Almoxarifado
                </span>
                @if($kpiAlmoxarifado['estoque_critico'] > 0)
                <span class="inline-flex items-center gap-1 font-bold text-rose-600 dark:text-rose-400" title="Itens abaixo do estoque mínimo">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    {{ $kpiAlmoxarifado['estoque_critico'] }} Crítico
                </span>
                @else
                <span class="font-semibold text-emerald-600 dark:text-emerald-400">Estoque OK</span>
                @endif
                <a href="{{ route('almoxarifado.requisicoes.index') }}" class="font-bold text-brand-600 hover:underline dark:text-brand-400">Abrir →</a>
            </div>
        </div>

        <!-- CARD 4: CIDADÃOS & SECRETARIAS -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800 hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-purple-600 dark:text-purple-400">Cidadania & Estrutura</span>
                    <h3 class="mt-1 text-3xl font-extrabold text-gray-900 dark:text-white">
                        {{ $kpiGeral['total_cidadaos'] }}
                    </h3>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Cidadãos Cadastrados</p>
                </div>
                <div class="rounded-xl bg-purple-50 p-3 text-purple-600 dark:bg-purple-900/30 dark:text-purple-300">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3 text-xs dark:border-gray-700">
                <span class="font-medium text-gray-600 dark:text-gray-300">
                    {{ $kpiGeral['total_secretarias'] }} Secretarias Ativas
                </span>
                <span class="font-medium text-gray-500 dark:text-gray-400">
                    {{ $kpiGeral['total_materiais'] }} Itens Almox.
                </span>
                <a href="{{ route('clientes.index') }}" class="font-bold text-brand-600 hover:underline dark:text-brand-400">Ver →</a>
            </div>
        </div>

    </div>

    <!-- Linha 2: Mapa Operacional da Cidade (GIS) + Carga por Secretaria -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        
        <!-- MAPA OPERACIONAL DE ZELADORIA (2/3 da largura) -->
        <div class="lg:col-span-2 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800 flex flex-col">
            <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Mapa Operacional da Cidade em Tempo Real (GIS)
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Localização e status das ocorrências urbanas distribuídas pelos bairros</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 rounded-md bg-gray-100 px-2 py-1 text-[11px] font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                        {{ count($ocorrenciasMapa) }} Pontos Ativos
                    </span>
                    <a href="{{ route('zeladoria.index') }}" class="text-xs font-bold text-brand-600 hover:underline dark:text-brand-400">Ver Painel Completo →</a>
                </div>
            </div>

            <!-- Container do Mapa Leaflet -->
            <div id="mapaCentroComando" class="w-full h-80 rounded-xl border border-gray-200 dark:border-gray-700 z-10"></div>

            <div class="mt-3 flex flex-wrap items-center justify-between text-xs text-gray-500 dark:text-gray-400 pt-2 border-t border-gray-100 dark:border-gray-700">
                <div class="flex flex-wrap items-center gap-4">
                    <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-blue-500"></span> Iluminação</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-amber-500"></span> Asfalto / Vias</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-emerald-500"></span> Limpeza / Poda</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-cyan-500"></span> Drenagem / Bueiros</span>
                </div>
                <span class="text-[11px] italic">OpenStreetMap • Sem custos de API</span>
            </div>
        </div>

        <!-- CARGA DE TRABALHO POR SECRETARIA (1/3 da largura) -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800 flex flex-col justify-between">
            <div>
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            Carga por Secretaria
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Processos ativos sob custódia de cada pasta</p>
                    </div>
                    <a href="{{ route('secretarias.index') }}" class="text-xs font-bold text-brand-600 hover:underline dark:text-brand-400">Ver todas</a>
                </div>

                <div class="space-y-3.5">
                    @php
                        $maxProc = max(1, $secretariasCarga->max('total'));
                    @endphp
                    @forelse($secretariasCarga->take(6) as $sec)
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="font-bold text-gray-800 dark:text-gray-200 truncate max-w-[180px]" title="{{ $sec['nome'] }}">
                                {{ $sec['sigla'] }}
                            </span>
                            <span class="font-semibold text-gray-700 dark:text-gray-300">
                                {{ $sec['total'] }} processo(s)
                            </span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                            @php
                                $pct = round(($sec['total'] / $maxProc) * 100);
                                $barColor = $sec['total'] > 5 ? 'bg-rose-500' : ($sec['total'] > 2 ? 'bg-amber-500' : 'bg-brand-500');
                            @endphp
                            <div class="h-full rounded-full {{ $barColor }} transition-all duration-500" style="width: {{ max(6, $pct) }}%"></div>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-gray-400 text-center py-4">Nenhuma secretaria com processos no momento.</p>
                    @endforelse
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>Monitoramento de SLA</span>
                <span class="font-bold text-gray-900 dark:text-white">{{ $kpiProcessos['tramitando'] }} em andamento</span>
            </div>
        </div>

    </div>

    <!-- Linha 3: Feed de Atividades Recentes + Processos com Prioridade / Decisão -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        <!-- FEED DE TRAMITAÇÕES RECENTES (Zero Papel) -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="h-5 w-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Últimas Tramitações & Despachos (Zero Papel)
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Movimentação eletrônica oficial de documentos em tempo real</p>
                </div>
                <a href="{{ route('protocolo.index') }}" class="text-xs font-bold text-brand-600 hover:underline dark:text-brand-400">Ver todas</a>
            </div>

            <div class="flow-root">
                <ul class="-mb-8">
                    @forelse($ultimasTramitacoes as $tram)
                    <li>
                        <div class="relative pb-6">
                            @if(!$loop->last)
                            <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                            @endif
                            <div class="relative flex space-x-3">
                                <div>
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-blue-600 ring-4 ring-white dark:bg-blue-900/40 dark:text-blue-300 dark:ring-gray-800">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    </span>
                                </div>
                                <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                    <div>
                                        <p class="text-xs font-semibold text-gray-900 dark:text-white">
                                            <a href="{{ route('protocolo.show', $tram->processo_id) }}" class="text-brand-600 hover:underline dark:text-brand-400">
                                                {{ $tram->processo?->numero ?: 'Proc. #' . $tram->processo_id }}
                                            </a>
                                            <span class="font-normal text-gray-500 dark:text-gray-400">encaminhado de</span>
                                            <strong class="font-bold text-gray-700 dark:text-gray-300">{{ $tram->unidadeOrigem?->sigla ?: $tram->unidadeOrigem?->nome }}</strong>
                                            <span class="font-normal text-gray-500 dark:text-gray-400">para</span>
                                            <strong class="font-bold text-gray-700 dark:text-gray-300">{{ $tram->unidadeDestino?->sigla ?: $tram->unidadeDestino?->nome }}</strong>
                                        </p>
                                        @if($tram->despacho)
                                        <p class="mt-1 text-xs text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-700/50 p-2 rounded-lg italic">
                                            "{{ Str::limit($tram->despacho, 90) }}"
                                        </p>
                                        @endif
                                    </div>
                                    <div class="whitespace-nowrap text-right text-xs text-gray-400">
                                        {{ $tram->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    @empty
                    <li class="py-4 text-center text-xs text-gray-400">Nenhuma tramitação registrada recentemente.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <!-- PROCESSOS & DEMANDAS URGENTES REQUERENDO DECISÃO -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800 flex flex-col justify-between">
            <div>
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="flex h-2.5 w-2.5 rounded-full bg-rose-600 animate-pulse"></span>
                            Fila de Prioridade & Decisão do Gabinete
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Processos administrativos urgentes aguardando despacho</p>
                    </div>
                    <span class="inline-flex rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-bold text-rose-800 dark:bg-rose-900/40 dark:text-rose-300">
                        {{ $processosPrioritarios->count() }} Processo(s)
                    </span>
                </div>

                <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 text-[11px] font-bold uppercase text-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            <tr>
                                <th class="px-3.5 py-2.5">Protocolo</th>
                                <th class="px-3.5 py-2.5">Assunto</th>
                                <th class="px-3.5 py-2.5">Secretaria Atual</th>
                                <th class="px-3.5 py-2.5 text-right">Ação</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($processosPrioritarios as $proc)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                <td class="px-3.5 py-3 whitespace-nowrap font-bold text-gray-900 dark:text-white">
                                    <span class="inline-flex items-center gap-1">
                                        @if($proc->prioridade === 'urgente')
                                        <span class="h-1.5 w-1.5 rounded-full bg-rose-600"></span>
                                        @endif
                                        {{ $proc->numero }}
                                    </span>
                                </td>
                                <td class="px-3.5 py-3">
                                    <span class="font-medium text-gray-800 dark:text-gray-200 block truncate max-w-[200px]" title="{{ $proc->assunto }}">
                                        {{ $proc->assunto }}
                                    </span>
                                    <span class="text-[10px] text-gray-400">Interessado: {{ $proc->interessado?->nome ?: 'Cidadão' }}</span>
                                </td>
                                <td class="px-3.5 py-3 whitespace-nowrap">
                                    <span class="rounded bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                        {{ $proc->unidadeAtual?->sigla ?: $proc->unidadeAtual?->nome }}
                                    </span>
                                </td>
                                <td class="px-3.5 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('protocolo.show', $proc) }}" class="inline-flex items-center gap-1 rounded-lg bg-brand-50 px-2.5 py-1 text-[11px] font-bold text-brand-700 hover:bg-brand-100 dark:bg-brand-900/40 dark:text-brand-300">
                                        Despachar
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-gray-400">Nenhum processo urgente no momento.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Resumo inferior de Zeladoria Recente -->
            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between text-xs">
                <span class="text-gray-500 dark:text-gray-400">Última Demanda de Zeladoria:</span>
                @if($ultimasDemandas->isNotEmpty())
                <a href="{{ route('zeladoria.show', $ultimasDemandas->first()) }}" class="font-semibold text-amber-600 hover:underline dark:text-amber-400">
                    {{ $ultimasDemandas->first()->codigo_interno }} ({{ $ultimasDemandas->first()->bairro ?: 'Centro' }})
                </a>
                @else
                <span class="text-gray-400">—</span>
                @endif
            </div>
        </div>

    </div>

</div>

<!-- Script de Inicialização do Mapa Leaflet do Centro de Comando -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const pontos = @json($ocorrenciasMapa);
    
    // Coordenadas padrão (Nonoai-RS ou primeiro ponto)
    let centroLat = -27.3600;
    let centroLng = -52.7700;

    if (pontos.length > 0) {
        centroLat = pontos[0].lat;
        centroLng = pontos[0].lng;
    }

    const map = L.map('mapaCentroComando', {
        zoomControl: true,
        scrollWheelZoom: false
    }).setView([centroLat, centroLng], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    function esc(str) {
        if (str === null || str === undefined) return '';
        const div = document.createElement('div');
        div.textContent = String(str);
        return div.innerHTML;
    }

    const bounds = [];

    pontos.forEach(p => {
        if (!p.lat || !p.lng) return;

        bounds.push([p.lat, p.lng]);

        // Marcador circular estilizado por categoria
        const circleMarker = L.circleMarker([p.lat, p.lng], {
            radius: 8,
            fillColor: p.cor || '#0284c7',
            color: '#ffffff',
            weight: 2,
            opacity: 1,
            fillOpacity: 0.85
        }).addTo(map);

        const safeCodigo = esc(p.codigo);
        const safeCategoria = esc(p.categoria);
        const safeBairro = esc(p.bairro);
        const safeLogradouro = esc(p.logradouro);
        const safeRelato = esc((p.relato || '').substring(0, 70));
        const safeUrl = encodeURI(p.show_url || '#');

        const popupContent = `
            <div class="p-1 text-xs">
                <strong class="text-gray-900 dark:text-white block mb-1 font-bold text-sm">${safeCodigo}</strong>
                <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-700 popup-badge mb-1">${safeCategoria}</span>
                <p class="text-gray-600 popup-desc mb-1">${safeBairro} - ${safeLogradouro}</p>
                <p class="text-gray-500 popup-desc italic mb-2">"${safeRelato}..."</p>
                <a href="${safeUrl}" class="block text-center rounded bg-brand-600 text-white font-bold py-1 px-2 text-[10px] hover:bg-brand-700">Ver Demanda</a>
            </div>
        `;

        circleMarker.bindPopup(popupContent);
    });

    if (bounds.length > 1) {
        map.fitBounds(bounds, { padding: [30, 30] });
    }
});
</script>
@endsection
