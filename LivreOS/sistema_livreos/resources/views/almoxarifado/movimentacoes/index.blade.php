{{--
 * Componente da aplicação Phoenix - SIGEM (Zero Papel)
 * Kardex Municipal e Auditoria de Movimentações de Estoque
 *
 * @license AGPL-3.0
 --}}

@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Kardex de Estoque & Movimentações</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">Rastreabilidade integral de entradas por empenho e baixas por requisição pública</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('almoxarifado.requisicoes.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Requisições Setoriais
        </a>
        <button type="button" onclick="document.getElementById('modalEntrada').classList.remove('hidden')" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Registrar Entrada (Empenho / NF)
        </button>
    </div>
</div>

@if(session('success'))
<div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-sm font-medium text-green-800 dark:border-green-800 dark:bg-green-900/30 dark:text-green-300">
    {{ session('success') }}
</div>
@endif

<!-- Cards de Indicadores do Kardex -->
<div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total de Registros</p>
                <h3 class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_movimentacoes'] }}</h3>
            </div>
            <div class="rounded-lg bg-gray-100 p-3 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
        </div>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Entradas Registradas</p>
                <h3 class="mt-1 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $stats['entradas'] }}</h3>
            </div>
            <div class="rounded-lg bg-emerald-50 p-3 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
            </div>
        </div>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Saídas / Fornecimentos</p>
                <h3 class="mt-1 text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['saidas'] }}</h3>
            </div>
            <div class="rounded-lg bg-blue-50 p-3 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
            </div>
        </div>
    </div>
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Itens no Catálogo</p>
                <h3 class="mt-1 text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $stats['total_itens'] }}</h3>
            </div>
            <div class="rounded-lg bg-purple-50 p-3 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
        </div>
    </div>
</div>

<!-- Filtros de Busca -->
<div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
    <form method="GET" action="{{ route('almoxarifado.movimentacoes.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-4">
        <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Documento / Justificativa</label>
            <input type="text" name="busca" value="{{ request('busca') }}" placeholder="Empenho nº, REQ-..." class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Material Específico</label>
            <select name="produto_id" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                <option value="">Todos os Materiais</option>
                @foreach($produtos as $p)
                <option value="{{ $p->id }}" {{ request('produto_id') == $p->id ? 'selected' : '' }}>{{ $p->codigo_sku ? '['.$p->codigo_sku.'] ' : '' }}{{ $p->nome }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Almoxarifado</label>
            <select name="deposito_id" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                <option value="">Todos os Almoxarifados</option>
                @foreach($almoxarifados as $alm)
                <option value="{{ $alm->id }}" {{ request('deposito_id') == $alm->id ? 'selected' : '' }}>{{ $alm->codigo ? '['.$alm->codigo.'] ' : '' }}{{ $alm->nome }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Tipo de Movimentação</label>
            <select name="tipo" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                <option value="">Todas (Entrada/Saída)</option>
                <option value="entrada" {{ request('tipo') == 'entrada' ? 'selected' : '' }}>Entrada (Empenho/Doação)</option>
                <option value="saida" {{ request('tipo') == 'saida' ? 'selected' : '' }}>Saída (Requisição/OS)</option>
                <option value="ajuste" {{ request('tipo') == 'ajuste' ? 'selected' : '' }}>Ajuste de Inventário</option>
            </select>
        </div>
        <div class="sm:col-span-4 flex justify-end gap-2">
            <a href="{{ route('almoxarifado.movimentacoes.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Limpar</a>
            <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800 dark:bg-gray-100 dark:text-gray-900 dark:hover:bg-white">Filtrar Kardex</button>
        </div>
    </form>
</div>

<!-- Tabela de Movimentações (Kardex) -->
<div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                <tr>
                    <th class="px-4 py-3">Data / Hora</th>
                    <th class="px-4 py-3">Material</th>
                    <th class="px-4 py-3">Almoxarifado</th>
                    <th class="px-4 py-3 text-center">Tipo</th>
                    <th class="px-4 py-3 text-right">Qtd Movimentada</th>
                    <th class="px-4 py-3 text-right">Saldo Ant. ➜ Post.</th>
                    <th class="px-4 py-3">Documento Ref.</th>
                    <th class="px-4 py-3">Responsável</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($movimentacoes as $mov)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                    <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500">
                        {{ $mov->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="font-bold text-gray-900 dark:text-white">{{ $mov->produto?->nome }}</span>
                        <span class="block text-xs text-gray-400">SKU: {{ $mov->produto?->codigo_sku ?: '—' }}</span>
                    </td>
                    <td class="px-4 py-3 text-xs font-medium text-gray-700 dark:text-gray-300">
                        {{ $mov->deposito?->nome }}
                    </td>
                    <td class="px-4 py-3 text-center whitespace-nowrap">
                        @if($mov->tipo === 'entrada')
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                            Entrada
                        </span>
                        @elseif($mov->tipo === 'saida')
                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
                            Saída
                        </span>
                        @else
                        <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                            {{ ucfirst($mov->tipo) }}
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right whitespace-nowrap font-bold {{ $mov->tipo === 'entrada' ? 'text-emerald-600' : 'text-blue-600' }}">
                        {{ $mov->tipo === 'entrada' ? '+' : '-' }}{{ number_format($mov->quantidade, 2, ',', '.') }} {{ $mov->produto?->unidade ?: 'UN' }}
                    </td>
                    <td class="px-4 py-3 text-right whitespace-nowrap text-xs text-gray-500">
                        {{ number_format($mov->saldo_anterior, 2, ',', '.') }} ➜ <strong class="text-gray-900 dark:text-white">{{ number_format($mov->saldo_posterior, 2, ',', '.') }}</strong>
                    </td>
                    <td class="px-4 py-3 text-xs">
                        <span class="font-medium text-gray-800 dark:text-gray-200">{{ $mov->documento_ref ?: '—' }}</span>
                        @if($mov->justificativa)
                        <span class="block text-[11px] text-gray-400 truncate max-w-xs">{{ $mov->justificativa }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-600 dark:text-gray-400">
                        {{ $mov->user?->name ?: 'Sistema' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        Nenhuma movimentação de estoque encontrada.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($movimentacoes->hasPages())
    <div class="border-t border-gray-200 px-5 py-3 dark:border-gray-700">
        {{ $movimentacoes->links() }}
    </div>
    @endif
</div>

{{-- MODAL REGISTRAR ENTRADA POR EMPENHO / NF --}}
<div id="modalEntrada" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 hidden">
    <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl dark:bg-gray-800">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Registrar Entrada de Material</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
            Lançamento de suprimento ao estoque do almoxarifado decorrente de Nota de Empenho ou Nota Fiscal de fornecedor.
        </p>

        <form method="POST" action="{{ route('almoxarifado.movimentacoes.entrada') }}">
            @csrf

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold uppercase text-gray-700 dark:text-gray-300 mb-1">
                        Material <span class="text-red-500">*</span>
                    </label>
                    <select name="produto_id" required class="block w-full rounded-lg border border-gray-300 p-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Selecione o material...</option>
                        @foreach($produtos as $prod)
                        <option value="{{ $prod->id }}">{{ $prod->codigo_sku ? '['.$prod->codigo_sku.'] ' : '' }}{{ $prod->nome }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-gray-700 dark:text-gray-300 mb-1">
                        Almoxarifado de Destino <span class="text-red-500">*</span>
                    </label>
                    <select name="deposito_id" required class="block w-full rounded-lg border border-gray-300 p-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Selecione o almoxarifado...</option>
                        @foreach($almoxarifados as $alm)
                        <option value="{{ $alm->id }}">{{ $alm->codigo ? '['.$alm->codigo.'] ' : '' }}{{ $alm->nome }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold uppercase text-gray-700 dark:text-gray-300 mb-1">
                            Quantidade Recebida <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="quantidade" step="0.01" min="0.01" required placeholder="0.00" class="block w-full rounded-lg border border-gray-300 p-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-gray-700 dark:text-gray-300 mb-1">
                            Nº Empenho / NF <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="documento_ref" required placeholder="Ex.: Empenho 2026/0142" class="block w-full rounded-lg border border-gray-300 p-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-gray-700 dark:text-gray-300 mb-1">
                        Observações / Fornecedor (Opcional)
                    </label>
                    <textarea name="justificativa" rows="2" placeholder="Dados do fornecedor, lote recebido..." class="block w-full rounded-lg border border-gray-300 p-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modalEntrada').classList.add('hidden')" class="rounded-lg border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">
                    Cancelar
                </button>
                <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700">
                    Confirmar Entrada no Estoque
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
