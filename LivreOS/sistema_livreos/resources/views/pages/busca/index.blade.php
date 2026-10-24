{{--
 * Componente da aplicação Phoenix - SIGEM
 * Painel Consolidado de Resultados da Busca Global Municipal
 *
 * @license AGPL-3.0
 --}}

@extends('layouts.app')

@section('content')
<div class="busca-page space-y-6" x-data="{ abaAtiva: 'todos' }">

    <!-- Header da Busca -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-bold text-brand-700 dark:bg-brand-900/40 dark:text-brand-300">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Busca Integrada Municipal
                    </span>
                    <span class="text-xs text-gray-400">•</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        Hierarquia: {{ auth()->user()->is_admin ? 'Gabinete / Administrador' : (auth()->user()->cargo ?: 'Servidor Autorizado') }}
                    </span>
                </div>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white">
                    Resultados para: <span class="text-brand-600 dark:text-brand-400">"{{ $termo }}"</span>
                </h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Total de <strong>{{ $totalEncontrado }}</strong> registro(s) encontrado(s) nos módulos autorizados para o seu perfil.
                </p>
            </div>

            <!-- Formulário de Nova Busca -->
            <form action="{{ route('busca.global') }}" method="GET" class="flex items-center gap-2 max-w-md w-full">
                <div class="relative w-full">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="q" value="{{ $termo }}" placeholder="Refinar pesquisa municipal..."
                        class="w-full rounded-xl border border-gray-300 bg-gray-50 pl-9 pr-3 py-2 text-xs font-medium text-gray-900 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-700 dark:text-white" />
                </div>
                <button type="submit" class="rounded-xl bg-brand-600 px-4 py-2 text-xs font-bold text-white hover:bg-brand-700 transition">
                    Buscar
                </button>
            </form>
        </div>

        <!-- Abas de Navegação Dinâmica -->
        <div class="mt-6 flex flex-wrap items-center gap-2 border-t border-gray-100 dark:border-gray-700 pt-4 text-xs font-bold">
            <button type="button" @click="abaAtiva = 'todos'"
                :class="abaAtiva === 'todos' ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'"
                class="rounded-lg px-3 py-1.5 transition">
                Todos ({{ $totalEncontrado }})
            </button>

            @if($permissoes['protocolo'] && $resultados['processos']->count() > 0)
            <button type="button" @click="abaAtiva = 'processos'"
                :class="abaAtiva === 'processos' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'"
                class="rounded-lg px-3 py-1.5 transition flex items-center gap-1.5">
                <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                Processos Digital ({{ $resultados['processos']->count() }})
            </button>
            @endif

            @if($permissoes['zeladoria'] && $resultados['zeladoria']->count() > 0)
            <button type="button" @click="abaAtiva = 'zeladoria'"
                :class="abaAtiva === 'zeladoria' ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'"
                class="rounded-lg px-3 py-1.5 transition flex items-center gap-1.5">
                <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                Zeladoria & Obras ({{ $resultados['zeladoria']->count() }})
            </button>
            @endif

            @if($permissoes['almoxarifado'] && ($resultados['requisicoes']->count() > 0 || $resultados['materiais']->count() > 0))
            <button type="button" @click="abaAtiva = 'almoxarifado'"
                :class="abaAtiva === 'almoxarifado' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'"
                class="rounded-lg px-3 py-1.5 transition flex items-center gap-1.5">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                Almoxarifado & Materiais ({{ $resultados['requisicoes']->count() + $resultados['materiais']->count() }})
            </button>
            @endif

            @if($permissoes['cidadaos'] && $resultados['cidadaos']->count() > 0)
            <button type="button" @click="abaAtiva = 'cidadaos'"
                :class="abaAtiva === 'cidadaos' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'"
                class="rounded-lg px-3 py-1.5 transition flex items-center gap-1.5">
                <span class="h-2 w-2 rounded-full bg-purple-500"></span>
                Cidadãos ({{ $resultados['cidadaos']->count() }})
            </button>
            @endif

            @if($permissoes['secretarias'] && $resultados['secretarias']->count() > 0)
            <button type="button" @click="abaAtiva = 'secretarias'"
                :class="abaAtiva === 'secretarias' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'"
                class="rounded-lg px-3 py-1.5 transition flex items-center gap-1.5">
                <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
                Secretarias ({{ $resultados['secretarias']->count() }})
            </button>
            @endif

            @if(isset($permissoes['frota']) && $permissoes['frota'] && isset($resultados['veiculos']) && $resultados['veiculos']->count() > 0)
            <button type="button" @click="abaAtiva = 'frota'"
                :class="abaAtiva === 'frota' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'"
                class="rounded-lg px-3 py-1.5 transition flex items-center gap-1.5">
                <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                Frota Municipal ({{ $resultados['veiculos']->count() }})
            </button>
            @endif
        </div>
    </div>

    @if($totalEncontrado === 0)
    <!-- Estado Vazio -->
    <div class="rounded-2xl border border-gray-200 bg-white p-12 text-center shadow-theme-sm dark:border-gray-800 dark:bg-gray-800">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500">
            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
        <h3 class="mt-4 text-base font-bold text-gray-900 dark:text-white">Nenhum registro encontrado</h3>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
            Não encontramos correspondências para "<span class="font-semibold">{{ $termo }}</span>" nos módulos aos quais você possui permissão de acesso.
        </p>
        <div class="mt-6 flex justify-center gap-3">
            <a href="{{ route('dashboard') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">
                Voltar à Dashboard
            </a>
            @if($permissoes['protocolo'])
            <a href="{{ route('protocolo.create') }}" class="rounded-xl bg-brand-600 px-4 py-2 text-xs font-bold text-white hover:bg-brand-700">
                Autuar Novo Processo
            </a>
            @endif
        </div>
    </div>
    @endif

    <!-- SEÇÃO 1: PROCESSOS / PROTOCOLO DIGITAL -->
    @if($permissoes['protocolo'] && $resultados['processos']->count() > 0)
    <div x-show="abaAtiva === 'todos' || abaAtiva === 'processos'" class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold uppercase tracking-wider text-blue-700 dark:text-blue-400 flex items-center gap-2">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Processos & Protocolo Digital ({{ $resultados['processos']->count() }})
            </h2>
            <a href="{{ route('protocolo.index') }}" class="text-xs font-bold text-brand-600 hover:underline dark:text-brand-400">Ir para Protocolo →</a>
        </div>

        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
            @foreach($resultados['processos'] as $proc)
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs hover:border-blue-400 hover:shadow-sm dark:border-gray-800 dark:bg-gray-800 dark:hover:border-blue-500 transition">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('protocolo.show', $proc) }}" class="text-sm font-extrabold text-blue-600 hover:underline dark:text-blue-400">
                                {{ $proc->numero }}
                            </a>
                            @if($proc->prioridade === 'urgente')
                            <span class="inline-flex items-center gap-1 rounded bg-rose-100 px-1.5 py-0.5 text-[10px] font-bold text-rose-700 dark:bg-rose-900/40 dark:text-rose-300">
                                <span class="h-1 w-1 rounded-full bg-rose-600 animate-pulse"></span> Urgente
                            </span>
                            @endif
                        </div>
                        <h4 class="mt-1 text-xs font-bold text-gray-900 dark:text-white line-clamp-1">
                            {{ $proc->assunto }}
                        </h4>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                            Interessado: <strong class="text-gray-700 dark:text-gray-300">{{ $proc->interessado?->nome ?: 'Não informado' }}</strong>
                        </p>
                    </div>
                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                        {{ ucfirst($proc->status) }}
                    </span>
                </div>
                <div class="mt-3 flex items-center justify-between border-t border-gray-100 pt-2 text-[11px] text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    <span>Setor Atual: <strong>{{ $proc->unidadeAtual?->sigla ?: $proc->unidadeAtual?->nome ?: 'Triagem' }}</strong></span>
                    <a href="{{ route('protocolo.show', $proc) }}" class="font-bold text-brand-600 hover:underline dark:text-brand-400">Ver Processo →</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- SEÇÃO 2: ZELADORIA URBANA & OBRAS -->
    @if($permissoes['zeladoria'] && $resultados['zeladoria']->count() > 0)
    <div x-show="abaAtiva === 'todos' || abaAtiva === 'zeladoria'" class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 flex items-center gap-2">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Zeladoria Urbana & Demandas Públicas ({{ $resultados['zeladoria']->count() }})
            </h2>
            <a href="{{ route('zeladoria.index') }}" class="text-xs font-bold text-brand-600 hover:underline dark:text-brand-400">Ir para Zeladoria →</a>
        </div>

        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
            @foreach($resultados['zeladoria'] as $zel)
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs hover:border-amber-400 hover:shadow-sm dark:border-gray-800 dark:bg-gray-800 dark:hover:border-amber-500 transition">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('zeladoria.show', $zel) }}" class="text-sm font-extrabold text-amber-600 hover:underline dark:text-amber-400">
                                {{ $zel->codigo_interno ?: 'Demanda #' . $zel->id }}
                            </a>
                            <span class="rounded bg-amber-50 px-1.5 py-0.5 text-[10px] font-bold text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                                {{ $zel->categoriaZeladoria?->nome ?: 'Geral' }}
                            </span>
                        </div>
                        <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 mt-1">
                            {{ $zel->bairro ? $zel->bairro . ' - ' : '' }}{{ $zel->logradouro ?: 'Sem endereço' }}
                        </p>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 italic line-clamp-1 mt-0.5">
                            "{{ $zel->relato_cliente ?: 'Sem descrição detalhada' }}"
                        </p>
                    </div>
                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                        {{ ucfirst($zel->status ?? 'Aberto') }}
                    </span>
                </div>
                <div class="mt-3 flex items-center justify-between border-t border-gray-100 pt-2 text-[11px] text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    <span>Solicitante: <strong>{{ $zel->cliente?->nome ?: 'Cidadão Municipal' }}</strong></span>
                    <a href="{{ route('zeladoria.show', $zel) }}" class="font-bold text-brand-600 hover:underline dark:text-brand-400">Ver Demanda →</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- SEÇÃO 3: ALMOXARIFADO & SUPRIMENTOS -->
    @if($permissoes['almoxarifado'] && ($resultados['requisicoes']->count() > 0 || $resultados['materiais']->count() > 0))
    <div x-show="abaAtiva === 'todos' || abaAtiva === 'almoxarifado'" class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400 flex items-center gap-2">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                Almoxarifado & Materiais
            </h2>
            <a href="{{ route('almoxarifado.requisicoes.index') }}" class="text-xs font-bold text-brand-600 hover:underline dark:text-brand-400">Ir para Almoxarifado →</a>
        </div>

        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
            @foreach($resultados['requisicoes'] as $req)
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs hover:border-emerald-400 hover:shadow-sm dark:border-gray-800 dark:bg-gray-800 dark:hover:border-emerald-500 transition">
                <div class="flex items-start justify-between">
                    <div>
                        <a href="{{ route('almoxarifado.requisicoes.show', $req) }}" class="text-sm font-extrabold text-emerald-600 hover:underline dark:text-emerald-400">
                            {{ $req->numero ?: 'Req. #' . $req->id }}
                        </a>
                        <p class="text-xs font-bold text-gray-800 dark:text-gray-200 mt-0.5">
                            Setor: {{ $req->unidadeSolicitante?->sigla ?: $req->unidadeSolicitante?->nome ?: 'Geral' }}
                        </p>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 italic line-clamp-1 mt-0.5">
                            "{{ $req->justificativa }}"
                        </p>
                    </div>
                    <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                        {{ $req->situacao_formatada ?? ucfirst($req->situacao) }}
                    </span>
                </div>
                <div class="mt-3 flex items-center justify-between border-t border-gray-100 pt-2 text-[11px] text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    <span>Requisitante: <strong>{{ $req->solicitante?->name ?: 'Servidor' }}</strong></span>
                    <a href="{{ route('almoxarifado.requisicoes.show', $req) }}" class="font-bold text-brand-600 hover:underline dark:text-brand-400">Ver Requisição →</a>
                </div>
            </div>
            @endforeach

            @foreach($resultados['materiais'] as $mat)
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs hover:border-emerald-400 hover:shadow-sm dark:border-gray-800 dark:bg-gray-800 dark:hover:border-emerald-500 transition">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Item em Estoque</span>
                        <h4 class="text-xs font-extrabold text-gray-900 dark:text-white">
                            {{ $mat->nome }}
                        </h4>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                            Código: <strong class="text-gray-700 dark:text-gray-300">{{ $mat->codigo ?: 'S/C' }}</strong>
                            • Un: <strong>{{ $mat->unidade_medida ?: 'UN' }}</strong>
                        </p>
                    </div>
                    <span class="rounded bg-gray-100 px-2 py-0.5 text-[10px] font-bold text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                        Estoque: {{ $mat->estoque_atual ?? 0 }}
                    </span>
                </div>
                <div class="mt-3 flex items-center justify-between border-t border-gray-100 pt-2 text-[11px] text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    <span>Valor Ref.: <strong>R$ {{ number_format($mat->preco_custo ?? 0, 2, ',', '.') }}</strong></span>
                    <a href="{{ route('produtos.show', $mat) }}" class="font-bold text-brand-600 hover:underline dark:text-brand-400">Ver Material →</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- SEÇÃO 4: CIDADÃOS & CONTRIBUINTES -->
    @if($permissoes['cidadaos'] && $resultados['cidadaos']->count() > 0)
    <div x-show="abaAtiva === 'todos' || abaAtiva === 'cidadaos'" class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold uppercase tracking-wider text-purple-700 dark:text-purple-400 flex items-center gap-2">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Cidadãos & Contribuintes Cadastrados ({{ $resultados['cidadaos']->count() }})
            </h2>
            <a href="{{ route('clientes.index') }}" class="text-xs font-bold text-brand-600 hover:underline dark:text-brand-400">Ver Cidadãos →</a>
        </div>

        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
            @foreach($resultados['cidadaos'] as $cid)
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs hover:border-purple-400 hover:shadow-sm dark:border-gray-800 dark:bg-gray-800 dark:hover:border-purple-500 transition">
                <div class="flex items-start justify-between">
                    <div>
                        <a href="{{ route('clientes.show', $cid) }}" class="text-sm font-extrabold text-purple-600 hover:underline dark:text-purple-400">
                            {{ $cid->nome }}
                        </a>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            CPF/CNPJ: <strong class="text-gray-700 dark:text-gray-300">{{ $cid->cpf_cnpj ?: 'Não informado' }}</strong>
                        </p>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">
                            Tel: {{ $cid->telefone ?: 'Sem telefone' }} • E-mail: {{ $cid->email ?: 'Sem e-mail' }}
                        </p>
                    </div>
                    <span class="rounded-full bg-purple-50 px-2 py-0.5 text-[10px] font-bold text-purple-700 dark:bg-purple-900/40 dark:text-purple-300">
                        Cidadão
                    </span>
                </div>
                <div class="mt-3 flex items-center justify-between border-t border-gray-100 pt-2 text-[11px] text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    <span>Bairro: <strong>{{ $cid->bairro ?: 'Centro' }}</strong></span>
                    <a href="{{ route('clientes.show', $cid) }}" class="font-bold text-brand-600 hover:underline dark:text-brand-400">Ficha do Cidadão →</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- SEÇÃO 5: SECRETARIAS & SETORES -->
    @if($permissoes['secretarias'] && $resultados['secretarias']->count() > 0)
    <div x-show="abaAtiva === 'todos' || abaAtiva === 'secretarias'" class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-400 flex items-center gap-2">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Secretarias & Órgãos Municipais ({{ $resultados['secretarias']->count() }})
            </h2>
            <a href="{{ route('secretarias.index') }}" class="text-xs font-bold text-brand-600 hover:underline dark:text-brand-400">Ver todas →</a>
        </div>

        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
            @foreach($resultados['secretarias'] as $sec)
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs hover:border-indigo-400 hover:shadow-sm dark:border-gray-800 dark:bg-gray-800 dark:hover:border-indigo-500 transition">
                <div class="flex items-start justify-between">
                    <div>
                        <a href="{{ route('secretarias.show', $sec) }}" class="text-sm font-extrabold text-indigo-600 hover:underline dark:text-indigo-400">
                            {{ $sec->sigla }} - {{ $sec->nome }}
                        </a>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Responsável: <strong class="text-gray-700 dark:text-gray-300">{{ $sec->responsavel_nome ?: 'Gabinete' }}</strong>
                        </p>
                    </div>
                    <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-bold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                        Pasta
                    </span>
                </div>
                <div class="mt-3 flex items-center justify-between border-t border-gray-100 pt-2 text-[11px] text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    <span>E-mail: <strong>{{ $sec->email ?: 'prefeitura@municipio.gov.br' }}</strong></span>
                    <a href="{{ route('secretarias.show', $sec) }}" class="font-bold text-brand-600 hover:underline dark:text-brand-400">Acessar Pasta →</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- SEÇÃO 6: FROTA MUNICIPAL & VEÍCULOS -->
    @if(isset($permissoes['frota']) && $permissoes['frota'] && isset($resultados['veiculos']) && $resultados['veiculos']->count() > 0)
    <div x-show="abaAtiva === 'todos' || abaAtiva === 'frota'" class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold uppercase tracking-wider text-blue-700 dark:text-blue-400 flex items-center gap-2">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                Frota Municipal & Veículos Oficiais ({{ $resultados['veiculos']->count() }})
            </h2>
            <a href="{{ route('frota.index') }}" class="text-xs font-bold text-brand-600 hover:underline dark:text-brand-400">Garagem Municipal →</a>
        </div>

        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">
            @foreach($resultados['veiculos'] as $veic)
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs hover:border-blue-400 hover:shadow-sm dark:border-gray-800 dark:bg-gray-800 dark:hover:border-blue-500 transition">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="rounded bg-gray-100 px-2 py-0.5 font-mono text-xs font-extrabold text-gray-900 dark:bg-gray-700 dark:text-white">
                                {{ $veic->placa }}
                            </span>
                            <span class="text-xs font-bold text-gray-900 dark:text-white">{{ $veic->modelo }}</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ $veic->marca }} • {{ $veic->ano_fabricacao }} • {{ $veic->combustivel }}
                        </p>
                    </div>
                    {!! $veic->status_badge !!}
                </div>
                <div class="mt-3 flex items-center justify-between border-t border-gray-100 pt-2 text-[11px] text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    <span>Lotação: <strong>{{ $veic->unidadeAdministrativa?->sigla ?: 'Garagem Central' }}</strong></span>
                    <a href="{{ route('frota.show', $veic) }}" class="font-bold text-brand-600 hover:underline dark:text-brand-400">Ficha Técnica →</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
