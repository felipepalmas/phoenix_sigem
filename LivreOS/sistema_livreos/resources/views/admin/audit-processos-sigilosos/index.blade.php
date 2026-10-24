{{--
 * Componente da aplicação Phoenix - SIGEM
 * Trilha de Auditoria de Processos Sigilosos e Restritos (LAI / LGPD)
 *
 * @license AGPL-3.0
 --}}

@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <span class="rounded bg-rose-100 px-2 py-0.5 text-xs font-black text-rose-800 dark:bg-rose-900/40 dark:text-rose-300">
                SEGURANÇA & LGPD
            </span>
            <span class="text-xs text-gray-400">Lei 12.527/2011 (LAI) • Lei 13.709/2018 (LGPD)</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Auditoria de Processos Sigilosos e Restritos</h1>
        <p class="text-xs text-gray-500 dark:text-gray-400">Rastreabilidade completa de acessos a expedientes municipais protegidos por sigilo legal ou fiscal.</p>
    </div>
</div>

{{-- Filtros de Busca --}}
<div class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
    <form method="GET" action="{{ route('admin.audit-processos-sigilosos.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-5 items-end">
        <div>
            <label class="mb-1 block text-xs font-semibold text-gray-700 dark:text-gray-300">Nº do Processo</label>
            <input type="text" name="processo_numero" value="{{ request('processo_numero') }}" placeholder="Ex: 2026/000001"
                class="w-full rounded-xl border border-gray-300 px-3 py-2 text-xs text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
        </div>
        <div>
            <label class="mb-1 block text-xs font-semibold text-gray-700 dark:text-gray-300">Servidor</label>
            <select name="user_id" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-xs text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                <option value="">Todos os servidores</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-semibold text-gray-700 dark:text-gray-300">Ação Auditada</label>
            <select name="acao" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-xs text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                <option value="">Todas as ações</option>
                <option value="visualizacao" {{ request('acao') === 'visualizacao' ? 'selected' : '' }}>Visualização dos Autos</option>
                <option value="download_documento" {{ request('acao') === 'download_documento' ? 'selected' : '' }}>Download de Peça</option>
                <option value="despacho" {{ request('acao') === 'despacho' ? 'selected' : '' }}>Tramitação / Despacho</option>
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-semibold text-gray-700 dark:text-gray-300">Data Inicial</label>
            <input type="date" name="data_inicio" value="{{ request('data_inicio') }}"
                class="w-full rounded-xl border border-gray-300 px-3 py-2 text-xs text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="flex-1 rounded-xl bg-brand-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-brand-700 transition">
                Filtrar
            </button>
            <a href="{{ route('admin.audit-processos-sigilosos.index') }}" class="rounded-xl border border-gray-300 px-3 py-2.5 text-xs font-semibold text-gray-600 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300">
                Limpar
            </a>
        </div>
    </form>
</div>

{{-- Tabela de Registros Auditados --}}
<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-left text-xs dark:divide-gray-700">
            <thead class="bg-gray-50 text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:bg-gray-900/50 dark:text-gray-400">
                <tr>
                    <th class="px-5 py-3.5">Data / Hora</th>
                    <th class="px-5 py-3.5">Processo Administrativo</th>
                    <th class="px-5 py-3.5">Grau de Sigilo</th>
                    <th class="px-5 py-3.5">Servidor Acessante</th>
                    <th class="px-5 py-3.5">Unidade / Secretaria</th>
                    <th class="px-5 py-3.5">Ação</th>
                    <th class="px-5 py-3.5">IP de Origem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-medium">
                @forelse($items as $audit)
                    <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/30 transition">
                        <td class="whitespace-nowrap px-5 py-3 text-gray-700 dark:text-gray-300 font-mono text-[11px]">
                            {{ $audit->created_at->format('d/m/Y H:i:s') }}
                        </td>
                        <td class="whitespace-nowrap px-5 py-3">
                            @if($audit->processo)
                                <a href="{{ route('protocolo.show', $audit->processo) }}" class="font-mono font-bold text-brand-600 hover:underline dark:text-brand-400">
                                    {{ $audit->processo->numero }}
                                </a>
                                <span class="block text-[11px] text-gray-500 truncate max-w-xs">{{ $audit->processo->assunto }}</span>
                            @else
                                <span class="text-gray-400 italic">Processo removido (#{{ $audit->processo_id }})</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-5 py-3">
                            @php
                                $sigilo = $audit->processo?->sigilo ?? 'sigiloso';
                            @endphp
                            @if($sigilo === 'sigiloso')
                                <span class="inline-flex items-center gap-1 rounded bg-rose-100 px-2 py-0.5 text-[10px] font-bold text-rose-800 dark:bg-rose-900/40 dark:text-rose-300">
                                    🔒 SIGILOSO
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                    ⚠️ RESTRITO
                                </span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-5 py-3 text-gray-900 dark:text-white font-semibold">
                            {{ $audit->user?->name ?? 'Sistema / Externo' }}
                            @if($audit->user?->email)
                                <span class="block text-[10px] font-normal text-gray-500">{{ $audit->user->email }}</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-5 py-3 text-gray-600 dark:text-gray-300 text-[11px]">
                            {{ $audit->unidade?->sigla ? $audit->unidade->sigla . ' - ' . $audit->unidade->nome : 'Geral' }}
                        </td>
                        <td class="whitespace-nowrap px-5 py-3">
                            <span class="rounded bg-blue-100 px-2 py-0.5 text-[10px] font-black uppercase text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                {{ strtoupper($audit->acao) }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-5 py-3 font-mono text-[11px] text-gray-500 dark:text-gray-400">
                            {{ $audit->ip_address ?? '127.0.0.1' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-8 text-center text-xs text-gray-500 dark:text-gray-400">
                            Nenhum registro de acesso a processos sigilosos encontrado no período.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($items->hasPages())
        <div class="border-t border-gray-100 p-4 dark:border-gray-700">
            {{ $items->links() }}
        </div>
    @endif
</div>
@endsection
