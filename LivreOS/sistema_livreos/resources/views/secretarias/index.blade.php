{{--
 * Componente da aplicação Phoenix - SIGEM
 * Módulo de Secretarias e Estrutura Organizacional Municipal
 *
 * @license AGPL-3.0
 --}}

@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Secretarias e Departamentos Municipais</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">Estrutura administrativa oficial da Prefeitura Municipal (Secretarias, Gabinete e Setores)</p>
    </div>
    <a href="{{ route('secretarias.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 transition">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Nova Secretaria / Unidade
    </a>
</div>

@if(session('success'))
<div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-sm font-medium text-green-800 dark:border-green-800 dark:bg-green-900/30 dark:text-green-300">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-800 dark:border-red-800 dark:bg-red-900/30 dark:text-red-300">
    {{ session('error') }}
</div>
@endif

<!-- Filtros de Busca -->
<div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
    <form method="GET" action="{{ route('secretarias.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="lg:col-span-2">
            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">Buscar por sigla, nome ou e-mail</label>
            <input type="text" name="busca" value="{{ request('busca') }}" placeholder="Ex: SMAS, Saúde, obras@prefeitura.gov.br..." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">Tipo de Unidade</label>
            <select name="tipo" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                <option value="">Todos os Tipos</option>
                <option value="gabinete" {{ request('tipo') == 'gabinete' ? 'selected' : '' }}>Gabinete</option>
                <option value="secretaria" {{ request('tipo') == 'secretaria' ? 'selected' : '' }}>Secretaria</option>
                <option value="departamento" {{ request('tipo') == 'departamento' ? 'selected' : '' }}>Departamento</option>
                <option value="divisao" {{ request('tipo') == 'divisao' ? 'selected' : '' }}>Divisão</option>
                <option value="setor" {{ request('tipo') == 'setor' ? 'selected' : '' }}>Setor</option>
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="w-full rounded-lg bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-900 dark:bg-gray-700 dark:hover:bg-gray-600 transition">Filtrar</button>
            @if(request()->hasAny(['busca', 'tipo', 'ativo']))
            <a href="{{ route('secretarias.index') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300" title="Limpar">✕</a>
            @endif
        </div>
    </form>
</div>

<!-- Tabela de Secretarias -->
<div class="rounded-xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-800 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                <tr>
                    <th class="px-5 py-3.5">Sigla / Nome</th>
                    <th class="px-5 py-3.5">Tipo / Subordinação</th>
                    <th class="px-5 py-3.5">Responsável (Titular)</th>
                    <th class="px-5 py-3.5">Contatos Oficiais</th>
                    <th class="px-5 py-3.5 text-center">Processos Ativos</th>
                    <th class="px-5 py-3.5 text-center">Situação</th>
                    <th class="px-5 py-3.5 text-right">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                @forelse($unidades as $unidade)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                    <td class="px-5 py-3.5">
                        <div class="font-bold text-gray-900 dark:text-white">
                            @if($unidade->sigla)
                                <span class="rounded bg-brand-50 px-2 py-0.5 text-xs font-bold text-brand-700 dark:bg-brand-900/40 dark:text-brand-300 mr-1.5">{{ $unidade->sigla }}</span>
                            @endif
                            {{ $unidade->nome }}
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-gray-600 dark:text-gray-300">
                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium capitalize bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200">
                            {{ $unidade->tipo }}
                        </span>
                        @if($unidade->parent)
                            <div class="text-xs text-gray-400 mt-0.5">Subordinada a: {{ $unidade->parent->sigla ?: $unidade->parent->nome }}</div>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-gray-700 dark:text-gray-300">
                        @if($unidade->responsavel)
                            <div class="font-medium text-gray-900 dark:text-white">{{ $unidade->responsavel->name }}</div>
                            <div class="text-xs text-gray-400">{{ $unidade->responsavel->email }}</div>
                        @else
                            <span class="text-xs text-gray-400 italic">Não designado</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-gray-600 dark:text-gray-400 text-xs">
                        @if($unidade->email)
                            <div>📧 {{ $unidade->email }}</div>
                        @endif
                        @if($unidade->telefone)
                            <div>📞 {{ $unidade->telefone }}</div>
                        @endif
                        @if(!$unidade->email && !$unidade->telefone)
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-center">
                        @if($unidade->processos_atuais_count > 0)
                            <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-bold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                {{ $unidade->processos_atuais_count }} processos
                            </span>
                        @else
                            <span class="text-xs text-gray-400">0</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-center">
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $unidade->ativo ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
                            {{ $unidade->ativo ? 'Ativa' : 'Inativa' }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                        <a href="{{ route('secretarias.edit', $unidade) }}" class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 mr-1">
                            Editar
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                        Nenhuma secretaria ou departamento encontrado.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($unidades->hasPages())
    <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-700">
        {{ $unidades->links() }}
    </div>
    @endif
</div>
@endsection
