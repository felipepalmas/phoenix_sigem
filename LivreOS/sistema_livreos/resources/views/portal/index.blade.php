{{--
 * Componente da aplicação Phoenix - SIGEM
 * Página Inicial do Portal de Serviços ao Cidadão & Transparência
 *
 * @license AGPL-3.0
 --}}

@extends('layouts.portal')

@section('content')
<div class="space-y-10">

    <!-- Mensagens de Alerta / Flash -->
    @if(session('warning'))
        <div class="rounded-2xl border border-amber-300 bg-amber-50 p-4 dark:border-amber-700/60 dark:bg-amber-900/30">
            <div class="flex items-center gap-3">
                <div class="rounded-xl bg-amber-100 p-2 text-amber-800 dark:bg-amber-800/40 dark:text-amber-300">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <p class="text-sm font-semibold text-amber-900 dark:text-amber-200">{{ session('warning') }}</p>
            </div>
        </div>
    @endif

    <!-- Hero Section Institucional & Busca Central -->
    <div class="relative overflow-hidden rounded-3xl border border-gray-200 bg-gradient-to-br from-brand-600 via-brand-700 to-indigo-900 px-6 py-12 text-white shadow-xl sm:px-12 sm:py-16">
        <!-- Detalhes de Fundo Abstratos -->
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/10 blur-2xl"></div>
        <div class="absolute -bottom-16 -left-16 h-64 w-64 rounded-full bg-indigo-500/20 blur-2xl"></div>

        <div class="relative mx-auto max-w-3xl text-center">
            <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3.5 py-1 text-xs font-bold backdrop-blur-md">
                <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                PREFEITURA MUNICIPAL 100% DIGITAL
            </span>
            <h1 class="mt-4 text-3xl font-black tracking-tight sm:text-4xl lg:text-5xl">
                O que você precisa resolver hoje?
            </h1>
            <p class="mt-3 text-sm text-blue-100 sm:text-base">
                Consulte processos administrativos, solicite serviços de zeladoria na sua rua ou valide a autenticidade de certidões públicas.
            </p>

            <!-- Campo de Busca Instantânea de Protocolo -->
            <form action="{{ route('portal.processos') }}" method="GET" class="mt-8">
                <div class="relative flex items-center shadow-2xl rounded-2xl bg-white p-2 text-gray-900 focus-within:ring-4 focus-within:ring-brand-400/40">
                    <div class="pointer-events-none pl-3 text-gray-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="numero" placeholder="Digite o nº do Processo ou Demanda (Ex: 2026/000001 ou ZEL-2026/00001)..." required
                        class="w-full border-0 bg-transparent px-3 py-2.5 text-sm font-medium text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-0 sm:text-base">
                    <button type="submit" class="rounded-xl bg-brand-600 px-6 py-3 text-xs sm:text-sm font-bold text-white hover:bg-brand-700 transition shrink-0">
                        Consultar
                    </button>
                </div>
                <p class="mt-2.5 text-xs text-blue-200">
                    Acesso público e imediato nos termos da Lei Federal de Governo Digital nº 14.129/2021.
                </p>
            </form>
        </div>
    </div>

    <!-- 4 Cartões Principais de Autoatendimento -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-extrabold text-gray-900 dark:text-white">Serviços Digitais em Destaque</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">Atendimento sem filas, sem burocracia e 100% eletrônico.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">

            <!-- Card 1: Ouvidoria e Zeladoria Urbana -->
            <a href="{{ route('portal.zeladoria.nova') }}" class="group rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm hover:border-brand-500 hover:shadow-md dark:border-gray-800 dark:bg-gray-800 dark:hover:border-brand-500 transition flex flex-col justify-between">
                <div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 mb-4 group-hover:scale-110 transition">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <span class="rounded bg-amber-50 px-2 py-0.5 text-[10px] font-black uppercase text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                        OUVIDORIA MUNICIPAL
                    </span>
                    <h3 class="mt-2 text-base font-bold text-gray-900 dark:text-white group-hover:text-brand-600 dark:group-hover:text-brand-400 transition">
                        Solicitar Serviço de Zeladoria
                    </h3>
                    <p class="mt-1.5 text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                        Reporte lâmpada apagada, buraco no asfalto, poda de árvore ou desobstrução de esgoto direto para a Secretaria de Obras com envio de foto.
                    </p>
                </div>
                <div class="mt-5 flex items-center gap-1.5 text-xs font-bold text-brand-600 dark:text-brand-400">
                    <span>Abrir Chamado Agora</span>
                    <svg class="h-4 w-4 transform group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>

            <!-- Card 2: Consulta de Processo Zero Papel -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800 flex flex-col justify-between">
                <div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 mb-4">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <span class="rounded bg-blue-50 px-2 py-0.5 text-[10px] font-black uppercase text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                        ZERO PAPEL
                    </span>
                    <h3 class="mt-2 text-base font-bold text-gray-900 dark:text-white">
                        Consultar Processo Administrativo
                    </h3>
                    <p class="mt-1.5 text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                        Acompanhe o andamento de alvarás, certidões, pedidos e requerimentos tramitados eletronicamente entre as secretarias.
                    </p>
                </div>
                <form action="{{ route('portal.processos') }}" method="GET" class="mt-5 flex gap-2">
                    <input type="text" name="numero" placeholder="Nº do processo..." required
                        class="w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 text-xs text-gray-900 focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <button type="submit" class="rounded-xl bg-blue-600 px-3 py-2 text-xs font-bold text-white hover:bg-blue-700 transition">
                        Ir
                    </button>
                </form>
            </div>

            <!-- Card 3: Validador de Autenticidade Documental -->
            <a href="{{ route('portal.autenticidade') }}" class="group rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm hover:border-emerald-500 hover:shadow-md dark:border-gray-800 dark:bg-gray-800 dark:hover:border-emerald-500 transition flex flex-col justify-between">
                <div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 mb-4 group-hover:scale-110 transition">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <span class="rounded bg-emerald-50 px-2 py-0.5 text-[10px] font-black uppercase text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                        CONFIABILIDADE JURÍDICA
                    </span>
                    <h3 class="mt-2 text-base font-bold text-gray-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition">
                        Verificar Autenticidade de Documentos
                    </h3>
                    <p class="mt-1.5 text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                        Confirme a integridade de pareceres, despachos e certidões emitidas pelo município via conferência do Hash Criptográfico SHA-256.
                    </p>
                </div>
                <div class="mt-5 flex items-center gap-1.5 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                    <span>Validar Hash ou QR Code</span>
                    <svg class="h-4 w-4 transform group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>

        </div>
    </div>

    <!-- Indicadores de Transparência Pública (Dados Abertos) -->
    <div class="rounded-3xl border border-gray-200 bg-white p-8 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800">
        <div class="text-center max-w-xl mx-auto mb-8">
            <h2 class="text-lg font-extrabold text-gray-900 dark:text-white sm:text-xl">
                Transparência Ativa da Gestão Pública
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Acompanhe o volume de atendimentos e serviços prestados em benefício da comunidade.
            </p>
        </div>

        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <div class="rounded-2xl bg-gray-50 p-5 text-center dark:bg-gray-700/40">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Processos Digitais</span>
                <p class="mt-2 font-mono text-3xl font-extrabold text-blue-600 dark:text-blue-400">{{ $stats['processos_total'] }}</p>
                <span class="text-[11px] text-gray-500 mt-1 block">Economia de papel e tempo</span>
            </div>

            <div class="rounded-2xl bg-gray-50 p-5 text-center dark:bg-gray-700/40">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Processos Concluídos</span>
                <p class="mt-2 font-mono text-3xl font-extrabold text-emerald-600 dark:text-emerald-400">{{ $stats['processos_concluidos'] }}</p>
                <span class="text-[11px] text-gray-500 mt-1 block">Decisões oficiais emitidas</span>
            </div>

            <div class="rounded-2xl bg-gray-50 p-5 text-center dark:bg-gray-700/40">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Demandas de Zeladoria</span>
                <p class="mt-2 font-mono text-3xl font-extrabold text-amber-600 dark:text-amber-400">{{ $stats['zeladoria_atendida'] + $stats['zeladoria_ativa'] }}</p>
                <span class="text-[11px] text-gray-500 mt-1 block">{{ $stats['zeladoria_atendida'] }} atendidas</span>
            </div>

            <div class="rounded-2xl bg-gray-50 p-5 text-center dark:bg-gray-700/40">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Frota Municipal Ativa</span>
                <p class="mt-2 font-mono text-3xl font-extrabold text-purple-600 dark:text-purple-400">{{ $stats['veiculos_operacao'] }}</p>
                <span class="text-[11px] text-gray-500 mt-1 block">Veículos a serviço público</span>
            </div>
        </div>
    </div>

    <!-- Guia Rápido de Secretarias Municipais -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-extrabold text-gray-900 dark:text-white">Secretarias & Órgãos de Atendimento</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">Estrutura organizacional municipal a serviço da população.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($secretarias as $sec)
                <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="rounded bg-brand-50 px-2 py-0.5 font-mono text-xs font-extrabold text-brand-700 dark:bg-brand-900/40 dark:text-brand-300">
                            {{ $sec->sigla }}
                        </span>
                        <h4 class="text-xs font-bold text-gray-900 dark:text-white truncate" title="{{ $sec->nome }}">
                            {{ $sec->nome }}
                        </h4>
                    </div>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400">
                        Responsável: <strong class="text-gray-700 dark:text-gray-300">{{ $sec->responsavel?->name ?: 'Gabinete' }}</strong>
                    </p>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                        E-mail: <strong>{{ $sec->email ?: 'prefeitura@municipio.gov.br' }}</strong>
                    </p>
                </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
