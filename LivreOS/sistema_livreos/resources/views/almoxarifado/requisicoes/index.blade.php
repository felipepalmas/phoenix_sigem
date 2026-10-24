{{--
 * Componente da aplicação Phoenix - SIGEM (Zero Papel)
 * Módulo de Almoxarifado Setorial e Requisições de Materiais
 *
 * @license AGPL-3.0
 --}}

@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Requisições de Materiais — Almoxarifado</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">Solicitação, aprovação de chefia e suprimento interno setorial</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('almoxarifado.movimentacoes.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Kardex / Movimentações
        </a>
        <a href="{{ route('almoxarifado.requisicoes.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 transition">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nova Requisição de Material
        </a>
    </div>
</div>

@if(session('success'))
<div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-sm font-medium text-green-800 dark:border-green-800 dark:bg-green-900/30 dark:text-green-300">
    {{ session('success') }}
</div>
@endif

@if(session('warning'))
<div class="mb-6 rounded-lg border border-yellow-200 bg-yellow-50 p-4 text-sm font-medium text-yellow-800 dark:border-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">
    {{ session('warning') }}
</div>
@endif

<!-- Cards de Indicadores do Almoxarifado -->
<div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Aguardando Chefia</p>
                <h3 class="mt-1 text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $stats['pendente_aprovacao'] }}</h3>
            </div>
            <div class="rounded-lg bg-amber-50 p-3 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">No Almoxarifado (Aprovadas)</p>
                <h3 class="mt-1 text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['aprovadas'] }}</h3>
            </div>
            <div class="rounded-lg bg-blue-50 p-3 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
        </div>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Atendidas / Concluídas</p>
                <h3 class="mt-1 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $stats['atendidas'] }}</h3>
            </div>
            <div class="rounded-lg bg-emerald-50 p-3 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Estoque Crítico / Mínimo</p>
                <h3 class="mt-1 text-2xl font-bold text-rose-600 dark:text-rose-400">{{ $stats['materiais_criticos'] }}</h3>
            </div>
            <div class="rounded-lg bg-rose-50 p-3 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
        </div>
    </div>
</div>

<!-- Filtros de Busca -->
<div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
    <form method="GET" action="{{ route('almoxarifado.requisicoes.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-4">
        <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Buscar</label>
            <input type="text" name="busca" value="{{ request('busca') }}" placeholder="Nº requisição, motivo..." class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Secretaria / Setor Solicitante</label>
            <select name="unidade_id" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                <option value="">Todas as Secretarias</option>
                @foreach($unidades as $u)
                <option value="{{ $u->id }}" {{ request('unidade_id') == $u->id ? 'selected' : '' }}>{{ $u->sigla ? $u->sigla . ' - ' : '' }}{{ $u->nome }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Almoxarifado Atendedor</label>
            <select name="almoxarifado_id" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                <option value="">Todos os Almoxarifados</option>
                @foreach($almoxarifados as $alm)
                <option value="{{ $alm->id }}" {{ request('almoxarifado_id') == $alm->id ? 'selected' : '' }}>{{ $alm->codigo ? '['.$alm->codigo.'] ' : '' }}{{ $alm->nome }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Situação</label>
            <select name="situacao" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                <option value="">Todas as Situações</option>
                <option value="pendente_aprovacao" {{ request('situacao') == 'pendente_aprovacao' ? 'selected' : '' }}>Aguardando Aprovação Chefia</option>
                <option value="aprovada" {{ request('situacao') == 'aprovada' ? 'selected' : '' }}>Aprovada / No Almoxarifado</option>
                <option value="atendida" {{ request('situacao') == 'atendida' ? 'selected' : '' }}>Atendida / Separada</option>
                <option value="concluida" {{ request('situacao') == 'concluida' ? 'selected' : '' }}>Concluída (Recebimento Confirmado)</option>
                <option value="recusada" {{ request('situacao') == 'recusada' ? 'selected' : '' }}>Recusada</option>
            </select>
        </div>
        <div class="sm:col-span-4 flex justify-end gap-2">
            <a href="{{ route('almoxarifado.requisicoes.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Limpar</a>
            <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800 dark:bg-gray-100 dark:text-gray-900 dark:hover:bg-white">Filtrar</button>
        </div>
    </form>
</div>

<!-- Tabela de Requisições -->
<div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                <tr>
                    <th class="px-5 py-3">Número / Data</th>
                    <th class="px-5 py-3">Unidade Solicitante</th>
                    <th class="px-5 py-3">Almoxarifado</th>
                    <th class="px-5 py-3">Solicitante</th>
                    <th class="px-5 py-3">Qtd Itens</th>
                    <th class="px-5 py-3">Prioridade</th>
                    <th class="px-5 py-3">Situação</th>
                    <th class="px-5 py-3 text-right">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($requisicoes as $req)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">
                    <td class="px-5 py-3.5 whitespace-nowrap">
                        <span class="font-bold text-gray-900 dark:text-white">{{ $req->numero }}</span>
                        <span class="block text-xs text-gray-400">{{ $req->created_at->format('d/m/Y H:i') }}</span>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="font-medium text-gray-900 dark:text-white">{{ $req->unidadeSolicitante?->sigla ?: $req->unidadeSolicitante?->nome }}</span>
                        <span class="block text-xs text-gray-400 truncate max-w-xs">{{ $req->unidadeSolicitante?->nome }}</span>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $req->almoxarifado?->nome }}</span>
                    </td>
                    <td class="px-5 py-3.5 whitespace-nowrap">
                        <span class="text-xs font-medium text-gray-900 dark:text-white">{{ $req->solicitante?->name }}</span>
                    </td>
                    <td class="px-5 py-3.5 whitespace-nowrap">
                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                            {{ $req->itens->count() }} item(ns)
                        </span>
                    </td>
                    <td class="px-5 py-3.5 whitespace-nowrap">
                        @if($req->prioridade === 'urgente')
                        <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-800 dark:bg-red-900/30 dark:text-red-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-red-600 animate-pulse"></span> Urgente
                        </span>
                        @else
                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                            Normal
                        </span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 whitespace-nowrap">
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $req->badge_class }}">
                            {{ $req->situacao_formatada }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                        <a href="{{ route('almoxarifado.requisicoes.show', $req) }}" class="inline-flex items-center gap-1 rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-brand-50 hover:text-brand-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-brand-900/30 dark:hover:text-brand-300 transition">
                            Visualizar & Tramitar
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        Nenhuma requisição de material encontrada com os filtros selecionados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($requisicoes->hasPages())
    <div class="border-t border-gray-200 px-5 py-3 dark:border-gray-700">
        {{ $requisicoes->links() }}
    </div>
    @endif
</div>
@endsection
