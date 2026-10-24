{{--
 * Componente da aplicação Phoenix - SIGEM (Zero Papel)
 * Formulário de Solicitação de Materiais
 *
 * @license AGPL-3.0
 --}}

@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Nova Requisição de Material</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">Solicitação formal de suprimentos de materiais ao almoxarifado</p>
    </div>
    <a href="{{ route('almoxarifado.requisicoes.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Voltar à Listagem
    </a>
</div>

@if($errors->any())
<div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-800 dark:border-red-800 dark:bg-red-900/30 dark:text-red-300">
    <div class="font-bold">Por favor, corrija os erros abaixo:</div>
    <ul class="mt-1 list-disc pl-5">
        @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('almoxarifado.requisicoes.store') }}" id="formRequisicao">
    @csrf

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Coluna da Esquerda: Dados Gerais e Justificativa -->
        <div class="lg:col-span-1 space-y-6">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="h-5 w-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Identificação & Destino
                </h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase text-gray-700 dark:text-gray-300 mb-1">
                            Secretaria / Unidade Solicitante <span class="text-red-500">*</span>
                        </label>
                        <select name="unidade_solicitante_id" required class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <option value="">Selecione a Secretaria...</option>
                            @foreach($unidades as $u)
                            <option value="{{ $u->id }}" {{ old('unidade_solicitante_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->sigla ? '['.$u->sigla.'] ' : '' }}{{ $u->nome }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-gray-700 dark:text-gray-300 mb-1">
                            Almoxarifado Fornecedor <span class="text-red-500">*</span>
                        </label>
                        <select name="almoxarifado_id" required class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <option value="">Selecione o Almoxarifado...</option>
                            @foreach($almoxarifados as $alm)
                            <option value="{{ $alm->id }}" {{ old('almoxarifado_id') == $alm->id ? 'selected' : '' }}>
                                {{ $alm->codigo ? '['.$alm->codigo.'] ' : '' }}{{ $alm->nome }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-gray-700 dark:text-gray-300 mb-1">
                            Prioridade <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center gap-2 rounded-lg border border-gray-200 p-2.5 text-xs font-medium cursor-pointer hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-750">
                                <input type="radio" name="prioridade" value="normal" {{ old('prioridade', 'normal') === 'normal' ? 'checked' : '' }} class="text-brand-600 focus:ring-brand-500">
                                <span>Normal</span>
                            </label>
                            <label class="flex items-center gap-2 rounded-lg border border-red-200 p-2.5 text-xs font-medium cursor-pointer bg-red-50/50 hover:bg-red-50 text-red-800 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-300">
                                <input type="radio" name="prioridade" value="urgente" {{ old('prioridade') === 'urgente' ? 'checked' : '' }} class="text-red-600 focus:ring-red-500">
                                <span>Urgente</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-gray-700 dark:text-gray-300 mb-1">
                            Vínculo com Protocolo Digital (Opcional)
                        </label>
                        <select name="processo_id" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <option value="">Nenhum processo vinculado</option>
                            @foreach($processos as $proc)
                            <option value="{{ $proc->id }}" {{ old('processo_id') == $proc->id ? 'selected' : '' }}>
                                {{ $proc->numero }} — {{ Str::limit($proc->assunto, 40) }}
                            </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-400">Integra esta requisição aos autos do processo Zero Papel.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-gray-700 dark:text-gray-300 mb-1">
                            Justificativa da Solicitação <span class="text-red-500">*</span>
                        </label>
                        <textarea name="justificativa" rows="3" required placeholder="Explique a finalidade pública e setor de aplicação dos materiais..." class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">{{ old('justificativa') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Coluna da Direita: Itens de Material Solicitados -->
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="h-5 w-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            Materiais Solicitados
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Selecione os itens do catálogo municipal e informe a quantidade necessária</p>
                    </div>
                    <button type="button" id="btnAddItem" class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-800 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Adicionar Material
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm" id="tabelaItens">
                        <thead class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            <tr>
                                <th class="px-3 py-2.5">Material / Especificação</th>
                                <th class="px-3 py-2.5 w-24">Unidade</th>
                                <th class="px-3 py-2.5 w-28">Estoque Atual</th>
                                <th class="px-3 py-2.5 w-32">Qtd Solicitada</th>
                                <th class="px-3 py-2.5 w-12 text-center">Ação</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700" id="itensContainer">
                            {{-- Linhas adicionadas via JS --}}
                        </tbody>
                    </table>
                </div>

                <div id="emptyItensWarning" class="p-8 text-center text-sm text-gray-500 dark:text-gray-400 border border-dashed border-gray-300 dark:border-gray-700 rounded-lg mt-3">
                    Nenhum material adicionado ainda. Clique no botão <strong>"Adicionar Material"</strong> acima para montar a lista.
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('almoxarifado.requisicoes.index') }}" class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Cadastrar e Enviar Requisição
                </button>
            </div>
        </div>
    </div>
</form>

{{-- Template de Linha de Material --}}
<template id="itemRowTemplate">
    <tr class="item-row hover:bg-gray-50 dark:hover:bg-gray-700/30">
        <td class="px-3 py-2.5">
            <select name="itens[INDEX][produto_id]" required class="select-produto block w-full rounded-lg border border-gray-300 px-3 py-1.5 text-xs shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                <option value="">Selecione o material...</option>
                @foreach($materiais as $mat)
                <option value="{{ $mat->id }}" 
                    data-unidade="{{ $mat->unidade ?: 'UN' }}" 
                    data-estoque="{{ (float) $mat->estoque_quantidade }}"
                    data-minimo="{{ (float) $mat->estoque_minimo }}">
                    {{ $mat->codigo_sku ? '['.$mat->codigo_sku.'] ' : '' }}{{ $mat->nome }}
                </option>
                @endforeach
            </select>
        </td>
        <td class="px-3 py-2.5 text-xs font-semibold text-gray-500 dark:text-gray-400 txt-unidade">
            —
        </td>
        <td class="px-3 py-2.5 text-xs txt-estoque">
            <span class="text-gray-400">—</span>
        </td>
        <td class="px-3 py-2.5">
            <input type="number" name="itens[INDEX][quantidade]" step="0.01" min="0.01" required placeholder="0.00" class="block w-full rounded-lg border border-gray-300 px-3 py-1.5 text-xs shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
        </td>
        <td class="px-3 py-2.5 text-center">
            <button type="button" class="btn-remove-row text-red-500 hover:text-red-700 dark:hover:text-red-400 p-1 rounded">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
        </td>
    </tr>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let rowIndex = 0;
    const container = document.getElementById('itensContainer');
    const warning = document.getElementById('emptyItensWarning');
    const btnAdd = document.getElementById('btnAddItem');
    const template = document.getElementById('itemRowTemplate');

    function checkEmpty() {
        if (container.querySelectorAll('tr').length === 0) {
            warning.style.display = 'block';
        } else {
            warning.style.display = 'none';
        }
    }

    function addRow() {
        const clone = template.content.cloneNode(true);
        const tr = clone.querySelector('tr');
        
        tr.innerHTML = tr.innerHTML.replaceAll('INDEX', rowIndex);
        rowIndex++;

        const select = tr.querySelector('.select-produto');
        const txtUnidade = tr.querySelector('.txt-unidade');
        const txtEstoque = tr.querySelector('.txt-estoque');
        const btnRemove = tr.querySelector('.btn-remove-row');

        select.addEventListener('change', function () {
            const opt = select.options[select.selectedIndex];
            if (opt && opt.value) {
                const un = opt.getAttribute('data-unidade') || 'UN';
                const est = parseFloat(opt.getAttribute('data-estoque') || 0);
                const min = parseFloat(opt.getAttribute('data-minimo') || 0);

                txtUnidade.textContent = un;
                if (est <= 0) {
                    txtEstoque.innerHTML = `<span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">Zerado (0)</span>`;
                } else if (est <= min) {
                    txtEstoque.innerHTML = `<span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">${est} ${un} (Alerta)</span>`;
                } else {
                    txtEstoque.innerHTML = `<span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">${est} ${un}</span>`;
                }
            } else {
                txtUnidade.textContent = '—';
                txtEstoque.innerHTML = '<span class="text-gray-400">—</span>';
            }
        });

        btnRemove.addEventListener('click', function () {
            tr.remove();
            checkEmpty();
        });

        container.appendChild(tr);
        checkEmpty();
    }

    btnAdd.addEventListener('click', addRow);

    // Adiciona uma linha inicial por padrão
    addRow();
});
</script>
@endsection
