{{--
 * Componente da aplicação Phoenix - SIGEM (Zero Papel)
 * Módulo de Protocolo e Tramitação Digital
 *
 * @license AGPL-3.0
 --}}

@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Protocolo Digital — Zero Papel</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">Gestão e tramitação eletrônica de processos administrativos municipais</p>
    </div>
    <a href="{{ route('protocolo.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 transition">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Autuar Novo Processo
    </a>
</div>

@if(session('success'))
<div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-sm font-medium text-green-800 dark:border-green-800 dark:bg-green-900/30 dark:text-green-300">
    {{ session('success') }}
</div>
@endif

<!-- Cards de Estatísticas Operacionais -->
<div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Em Tramitação</p>
                <h3 class="mt-1 text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $totalTramitando }}</h3>
            </div>
            <div class="rounded-lg bg-blue-50 p-3 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Urgentes Ativos</p>
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
                <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Concluídos / Arquivados</p>
                <h3 class="mt-1 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $totalConcluidos }}</h3>
            </div>
            <div class="rounded-lg bg-emerald-50 p-3 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>
</div>

<!-- Filtros de Busca -->
<div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
    <form method="GET" action="{{ route('protocolo.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <div class="lg:col-span-2">
            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">Buscar por nº, assunto ou cidadão</label>
            <input type="text" name="busca" value="{{ request('busca') }}" placeholder="Ex: 2026/000001, Alvará, João Silva..." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:border-brand-500">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">Status</label>
            <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                <option value="">Todos os status</option>
                <option value="tramitando" {{ request('status') === 'tramitando' ? 'selected' : '' }}>Em Tramitação</option>
                <option value="deferido" {{ request('status') === 'deferido' ? 'selected' : '' }}>Deferido</option>
                <option value="indeferido" {{ request('status') === 'indeferido' ? 'selected' : '' }}>Indeferido</option>
                <option value="suspenso" {{ request('status') === 'suspenso' ? 'selected' : '' }}>Suspenso / Diligência</option>
                <option value="arquivado" {{ request('status') === 'arquivado' ? 'selected' : '' }}>Arquivado</option>
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">Unidade Atual</label>
            <select name="unidade_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                <option value="">Todas as Secretarias</option>
                @foreach($unidades as $unidade)
                <option value="{{ $unidade->id }}" {{ request('unidade_id') == $unidade->id ? 'selected' : '' }}>
                    {{ $unidade->nome_completo }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="w-full rounded-lg bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-900 dark:bg-gray-700 dark:hover:bg-gray-600 transition">Filtrar</button>
            @if(request()->hasAny(['busca', 'status', 'unidade_id', 'prioridade']))
            <a href="{{ route('protocolo.index') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300" title="Limpar">✕</a>
            @endif
        </div>
    </form>
</div>

<!-- Tabela de Processos -->
<div class="rounded-xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-800 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                <tr>
                    <th class="px-5 py-3.5">Nº do Protocolo</th>
                    <th class="px-5 py-3.5">Assunto</th>
                    <th class="px-5 py-3.5">Cidadão / Interessado</th>
                    <th class="px-5 py-3.5">Unidade Atual</th>
                    <th class="px-5 py-3.5 text-center">Prioridade</th>
                    <th class="px-5 py-3.5 text-center">Status</th>
                    <th class="px-5 py-3.5 text-right">Ação</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                @forelse($processos as $proc)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                    <td class="px-5 py-3.5 font-bold text-brand-600 dark:text-brand-400">
                        <a href="{{ route('protocolo.show', $proc) }}" class="hover:underline">
                            {{ $proc->numero }}
                        </a>
                        <div class="text-xs text-gray-400 font-normal mt-0.5">Criado em {{ $proc->created_at->format('d/m/Y H:i') }}</div>
                    </td>
                    <td class="px-5 py-3.5 font-medium text-gray-800 dark:text-gray-100">
                        {{ $proc->assunto }}
                    </td>
                    <td class="px-5 py-3.5 text-gray-600 dark:text-gray-300">
                        {{ $proc->interessado ? $proc->interessado->nome : 'Não informado' }}
                    </td>
                    <td class="px-5 py-3.5 text-gray-700 dark:text-gray-300">
                        <span class="inline-flex items-center gap-1.5 font-medium">
                            <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                            {{ $proc->unidadeAtual ? $proc->unidadeAtual->nome_completo : '—' }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-center">
                        @php $pBadge = $proc->prioridade_badge; @endphp
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $pBadge['bg'] }} {{ $pBadge['text'] }}">
                            {{ $pBadge['label'] }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-center">
                        @php $sBadge = $proc->status_badge; @endphp
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $sBadge['bg'] }} {{ $sBadge['text'] }}">
                            {{ $sBadge['label'] }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="{{ route('protocolo.show', $proc) }}" class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                            Acessar
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-12 text-center text-gray-500 dark:text-gray-400">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="h-12 w-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <p class="font-medium text-base text-gray-700 dark:text-gray-300">Nenhum processo administrativo encontrado</p>
                            <p class="text-xs text-gray-500 mt-1">Clique em "Autuar Novo Processo" para iniciar o trâmite digital.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($processos->hasPages())
    <div class="border-t border-gray-200 px-5 py-3 dark:border-gray-700">
        {{ $processos->links() }}
    </div>
    @endif
</div>
@endsection
