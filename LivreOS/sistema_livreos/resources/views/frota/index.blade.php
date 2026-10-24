{{--
 * Componente da aplicação Phoenix - SIGEM
 * Painel de Controle da Frota Municipal e Garagem Oficial
 *
 * @license AGPL-3.0
 --}}

@extends('layouts.app')

@section('content')
<div class="frota-page space-y-6">

    <!-- Header do Centro de Controle de Frota -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-100 px-3 py-1 text-xs font-bold text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                        <span class="h-2 w-2 rounded-full bg-blue-600 animate-pulse"></span>
                        MÓDULO D — TRANSPORTE & LOGÍSTICA PÚBLICA
                    </span>
                    <span class="text-xs text-gray-400">•</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Garagem Municipal Ativa</span>
                </div>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white sm:text-3xl">
                    Gestão de Frota & Diário de Bordo Digital
                </h1>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                    Controle de veículos, saídas/retornos, motoristas com alertas de CNH, consumo de combustível e manutenções.
                </p>
            </div>

            <!-- Ações Rápidas da Garagem -->
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('frota.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-brand-700 transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Cadastrar Veículo
                </a>
                <a href="{{ route('frota.viagens.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-xs font-bold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition">
                    <svg class="h-4 w-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Diário de Bordo
                </a>
                <a href="{{ route('frota.motoristas.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-xs font-bold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition">
                    <svg class="h-4 w-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Motoristas Oficiais
                </a>
            </div>
        </div>
    </div>

    <!-- Alertas de CNH Próxima ao Vencimento -->
    @if($motoristasCnhAlerta->isNotEmpty())
    <div class="rounded-xl border border-amber-300 bg-amber-50 p-4 dark:border-amber-700/60 dark:bg-amber-900/20">
        <div class="flex items-start gap-3">
            <div class="rounded-lg bg-amber-100 p-2 text-amber-800 dark:bg-amber-800/40 dark:text-amber-300">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div class="flex-1">
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-amber-900 dark:text-amber-200">
                    Atenção da Garagem: CNHs com Vencimento Próximo
                </h3>
                <div class="mt-1 flex flex-wrap items-center gap-3 text-xs text-amber-800 dark:text-amber-300">
                    @foreach($motoristasCnhAlerta as $mot)
                    <span class="inline-flex items-center gap-1.5 font-medium">
                        • <strong>{{ $mot->nome }}</strong> (Cat. {{ $mot->cnh_categoria }}):
                        <span class="underline">{{ $mot->cnh_validade->format('d/m/Y') }}</span>
                        ({{ $mot->cnh_validade->diffForHumans() }})
                    </span>
                    @endforeach
                </div>
            </div>
            <a href="{{ route('frota.motoristas.index') }}" class="text-xs font-bold text-amber-900 underline hover:text-amber-700 dark:text-amber-300">
                Gerenciar CNHs →
            </a>
        </div>
    </div>
    @endif

    <!-- Cards de Indicadores da Frota (KPIs) -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total da Frota</span>
                    <h3 class="mt-1 text-3xl font-extrabold text-gray-900 dark:text-white">{{ $kpis['total'] }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Veículos & Máquinas</p>
                </div>
                <div class="rounded-xl bg-gray-100 p-3 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Na Garagem</span>
                    <h3 class="mt-1 text-3xl font-extrabold text-gray-900 dark:text-white">{{ $kpis['disponiveis'] }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Prontos para saída</p>
                </div>
                <div class="rounded-xl bg-emerald-50 p-3 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-300">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Em Trânsito</span>
                    <h3 class="mt-1 text-3xl font-extrabold text-gray-900 dark:text-white">{{ $kpis['em_viagem'] }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Em Rota / Viagem</p>
                </div>
                <div class="rounded-xl bg-blue-50 p-3 text-blue-600 dark:bg-blue-900/30 dark:text-blue-300">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Em Oficina</span>
                    <h3 class="mt-1 text-3xl font-extrabold text-gray-900 dark:text-white">{{ $kpis['em_manutencao'] }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Manutenção / Revisão</p>
                </div>
                <div class="rounded-xl bg-amber-50 p-3 text-amber-600 dark:bg-amber-900/30 dark:text-amber-300">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Viagens em Andamento no Momento (Diário de Bordo Rápido) -->
    @if($viagensAtivas->isNotEmpty())
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-sm font-bold uppercase tracking-wider text-blue-700 dark:text-blue-400 flex items-center gap-2">
                <span class="h-2.5 w-2.5 rounded-full bg-blue-600 animate-pulse"></span>
                Diário de Bordo: Veículos em Trânsito / Em Rota Agora
            </h2>
            <a href="{{ route('frota.viagens.index') }}" class="text-xs font-bold text-brand-600 hover:underline dark:text-brand-400">Ver Todas as Viagens →</a>
        </div>

        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">
            @foreach($viagensAtivas as $via)
            <div class="rounded-xl border border-blue-200 bg-blue-50/40 p-3.5 dark:border-blue-900/50 dark:bg-blue-900/20">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="font-mono text-xs font-bold text-blue-800 dark:text-blue-300">{{ $via->numero }}</span>
                        <h4 class="text-xs font-extrabold text-gray-900 dark:text-white mt-0.5">
                            {{ $via->veiculo?->placa }} — {{ $via->veiculo?->modelo }}
                        </h4>
                        <p class="text-[11px] text-gray-600 dark:text-gray-300 mt-1">
                            Condutor: <strong>{{ $via->motorista?->nome }}</strong>
                        </p>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 line-clamp-1">
                            Destino: {{ $via->destino }}
                        </p>
                    </div>
                    <span class="rounded bg-blue-100 px-2 py-0.5 text-[10px] font-bold text-blue-800 dark:bg-blue-800 dark:text-blue-200">
                        Km Saída: {{ number_format($via->km_saida, 0, ',', '.') }}
                    </span>
                </div>
                <div class="mt-3 flex items-center justify-between border-t border-blue-100 pt-2 text-[11px] dark:border-blue-900/40">
                    <span class="text-gray-400">{{ $via->data_saida->diffForHumans() }}</span>
                    <a href="{{ route('frota.viagens.index') }}" class="font-bold text-brand-600 hover:underline dark:text-brand-400">
                        Registrar Retorno →
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Filtros e Grid de Veículos da Frota -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800">
        <form method="GET" action="{{ route('frota.index') }}" class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <div class="lg:col-span-2">
                <input type="text" name="busca" value="{{ request('busca') }}" placeholder="Buscar por placa, modelo, marca ou patrimônio..."
                    class="w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 text-xs text-gray-900 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-700 dark:text-white" />
            </div>

            <div>
                <select name="secretaria_id" class="w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 text-xs text-gray-900 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-700 dark:text-white">
                    <option value="">Todas as Secretarias</option>
                    @foreach($secretarias as $sec)
                    <option value="{{ $sec->id }}" {{ request('secretaria_id') == $sec->id ? 'selected' : '' }}>
                        {{ $sec->sigla }} - {{ $sec->nome }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="status" class="w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 text-xs text-gray-900 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-700 dark:text-white">
                    <option value="">Todos os Status</option>
                    <option value="disponivel" {{ request('status') == 'disponivel' ? 'selected' : '' }}>Disponível na Garagem</option>
                    <option value="em_viagem" {{ request('status') == 'em_viagem' ? 'selected' : '' }}>Em Viagem / Rota</option>
                    <option value="em_manutencao" {{ request('status') == 'em_manutencao' ? 'selected' : '' }}>Em Manutenção</option>
                    <option value="inoperante" {{ request('status') == 'inoperante' ? 'selected' : '' }}>Inoperante</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 rounded-xl bg-brand-600 px-3 py-2 text-xs font-bold text-white hover:bg-brand-700 transition">
                    Filtrar
                </button>
                <a href="{{ route('frota.index') }}" class="rounded-xl border border-gray-300 px-3 py-2 text-xs font-bold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">
                    Limpar
                </a>
            </div>
        </form>

        <!-- Grade de Veículos -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($veiculos as $v)
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs hover:border-brand-400 hover:shadow-md dark:border-gray-700 dark:bg-gray-800 transition">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="font-mono text-base font-extrabold text-brand-600 dark:text-brand-400">
                            {{ $v->placa }}
                        </span>
                        <h3 class="text-xs font-bold text-gray-900 dark:text-white line-clamp-1 mt-0.5" title="{{ $v->modelo }}">
                            {{ $v->modelo }}
                        </h3>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">
                            {{ $v->marca }} • Ano {{ $v->ano_fabricacao }}/{{ $v->ano_modelo }}
                        </p>
                    </div>
                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $v->status_badge }}">
                        {{ $v->status_formatado }}
                    </span>
                </div>

                <div class="mt-3 space-y-1.5 border-t border-gray-100 dark:border-gray-700 pt-3 text-xs">
                    <div class="flex items-center justify-between text-gray-600 dark:text-gray-300 text-[11px]">
                        <span>Secretaria:</span>
                        <strong class="truncate max-w-[170px]" title="{{ $v->unidadeAdministrativa?->nome }}">
                            {{ $v->unidadeAdministrativa?->sigla ?: 'Gabinete' }}
                        </strong>
                    </div>
                    <div class="flex items-center justify-between text-gray-600 dark:text-gray-300 text-[11px]">
                        <span>Hodômetro Atual:</span>
                        <strong>{{ number_format($v->km_atual, 0, ',', '.') }} km</strong>
                    </div>
                    <div class="flex items-center justify-between text-gray-600 dark:text-gray-300 text-[11px]">
                        <span>Tipo / Combustível:</span>
                        <span>{{ ucfirst($v->tipo) }} • {{ ucfirst($v->combustivel) }}</span>
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-between border-t border-gray-100 dark:border-gray-700 pt-2.5 text-xs">
                    <span class="text-[10px] text-gray-400 font-mono">{{ $v->patrimonio_tombamento ?: 'S/ Patrimônio' }}</span>
                    <a href="{{ route('frota.show', $v) }}" class="inline-flex items-center gap-1 font-bold text-brand-600 hover:underline dark:text-brand-400">
                        Ficha do Veículo
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
            @empty
            <div class="col-span-full py-12 text-center text-xs text-gray-400">
                Nenhum veículo encontrado com os filtros aplicados.
            </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $veiculos->links() }}
        </div>
    </div>

</div>
@endsection
