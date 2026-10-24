{{--
 * Componente da aplicação Phoenix - SIGEM (Zero Papel)
 * Visualização e Tramitação de Requisição de Materiais
 *
 * @license AGPL-3.0
 --}}

@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                Requisição {{ $requisicao->numero }}
            </h1>
            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $requisicao->badge_class }}">
                {{ $requisicao->situacao_formatada }}
            </span>
            @if($requisicao->prioridade === 'urgente')
            <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-bold text-red-800 dark:bg-red-900/30 dark:text-red-400">
                <span class="h-1.5 w-1.5 rounded-full bg-red-600 animate-pulse"></span> URGENTE
            </span>
            @endif
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">
            Solicitada em {{ $requisicao->created_at->format('d/m/Y \à\s H:i') }} por {{ $requisicao->solicitante?->name }}
        </p>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('almoxarifado.requisicoes.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Voltar
        </a>

        {{-- Ações conforme o estado da requisição --}}
        @if($requisicao->situacao === 'pendente_aprovacao')
            <button type="button" onclick="document.getElementById('modalRecusar').classList.remove('hidden')" class="inline-flex items-center gap-1.5 rounded-lg border border-red-300 bg-white px-3.5 py-2 text-sm font-semibold text-red-700 hover:bg-red-50 dark:border-red-800 dark:bg-gray-800 dark:text-red-400 dark:hover:bg-red-900/20 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Recusar
            </button>
            <button type="button" onclick="document.getElementById('modalAprovar').classList.remove('hidden')" class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Aprovar Requisição
            </button>
        @endif

        @if(in_array($requisicao->situacao, ['aprovada', 'atendida_parcial']))
            <button type="button" onclick="document.getElementById('modalAtender').classList.remove('hidden')" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                Atender e Baixar do Estoque
            </button>
        @endif

        @if($requisicao->situacao === 'atendida')
            <form method="POST" action="{{ route('almoxarifado.requisicoes.confirmar', $requisicao) }}" onsubmit="return confirm('Confirma que os materiais foram conferidos e recebidos pela sua secretaria?');">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-800 transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Confirmar Recebimento na Unidade
                </button>
            </form>
        @endif
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

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <!-- Coluna da Esquerda: Dados e Fluxo de Tramitação -->
    <div class="lg:col-span-1 space-y-6">
        <!-- Card de Informações Cadastrais -->
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
            <h2 class="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-4">
                Informações da Requisição
            </h2>

            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-xs text-gray-500 dark:text-gray-400">Secretaria / Setor Solicitante</dt>
                    <dd class="font-semibold text-gray-900 dark:text-white">
                        {{ $requisicao->unidadeSolicitante?->sigla ? '['.$requisicao->unidadeSolicitante?->sigla.'] ' : '' }}
                        {{ $requisicao->unidadeSolicitante?->nome }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs text-gray-500 dark:text-gray-400">Almoxarifado Fornecedor</dt>
                    <dd class="font-semibold text-gray-900 dark:text-white">
                        {{ $requisicao->almoxarifado?->nome }}
                    </dd>
                    @if($requisicao->almoxarifado?->localizacao)
                    <dd class="text-xs text-gray-400">{{ $requisicao->almoxarifado->localizacao }}</dd>
                    @endif
                </div>

                <div>
                    <dt class="text-xs text-gray-500 dark:text-gray-400">Servidor Solicitante</dt>
                    <dd class="font-medium text-gray-800 dark:text-gray-200">
                        {{ $requisicao->solicitante?->name }} ({{ $requisicao->solicitante?->email }})
                    </dd>
                </div>

                @if($requisicao->processo)
                <div class="rounded-lg border border-purple-200 bg-purple-50 p-3 dark:border-purple-900/50 dark:bg-purple-900/20">
                    <dt class="text-xs font-semibold text-purple-800 dark:text-purple-300">Processo Zero Papel Vinculado</dt>
                    <dd class="mt-1">
                        <a href="{{ route('protocolo.show', $requisicao->processo) }}" class="inline-flex items-center gap-1 text-xs font-bold text-purple-700 hover:underline dark:text-purple-400">
                            {{ $requisicao->processo->numero }}
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                        <p class="text-xs text-purple-600 dark:text-purple-400 truncate">{{ $requisicao->processo->assunto }}</p>
                    </dd>
                </div>
                @endif

                <div class="border-t border-gray-100 pt-3 dark:border-gray-700">
                    <dt class="text-xs text-gray-500 dark:text-gray-400 mb-1">Justificativa Pública</dt>
                    <dd class="rounded-lg bg-gray-50 p-3 text-xs text-gray-700 dark:bg-gray-900 dark:text-gray-300">
                        {{ $requisicao->justificativa }}
                    </dd>
                </div>
            </dl>
        </div>

        <!-- Linha do Tempo / Workflow -->
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
            <h2 class="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-4">
                Trâmite & Atendimento
            </h2>

            <ol class="relative border-l border-gray-200 ml-3 space-y-6 dark:border-gray-700">
                <!-- 1. Solicitação -->
                <li class="ml-6">
                    <span class="absolute -left-3 flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-blue-600 ring-8 ring-white dark:bg-blue-900 dark:text-blue-300 dark:ring-gray-800">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </span>
                    <h3 class="text-xs font-bold text-gray-900 dark:text-white">Solicitação Criada</h3>
                    <p class="text-xs text-gray-500">{{ $requisicao->created_at->format('d/m/Y H:i') }}</p>
                    <p class="text-xs text-gray-400">Por {{ $requisicao->solicitante?->name }}</p>
                </li>

                <!-- 2. Aprovação Chefia -->
                <li class="ml-6">
                    @if($requisicao->aprovado_em)
                        <span class="absolute -left-3 flex h-6 w-6 items-center justify-center rounded-full {{ $requisicao->situacao === 'recusada' ? 'bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-300' : 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900 dark:text-emerald-300' }} ring-8 ring-white dark:ring-gray-800">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        <h3 class="text-xs font-bold {{ $requisicao->situacao === 'recusada' ? 'text-red-700' : 'text-emerald-700' }} dark:text-white">
                            {{ $requisicao->situacao === 'recusada' ? 'Requisição Recusada' : 'Aprovada pela Chefia' }}
                        </h3>
                        <p class="text-xs text-gray-500">{{ $requisicao->aprovado_em->format('d/m/Y H:i') }}</p>
                        <p class="text-xs text-gray-400">Por {{ $requisicao->aprovadoPor?->name }}</p>
                        @if($requisicao->parecer_aprovacao)
                        <p class="mt-1 text-xs italic text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-900 p-2 rounded">"{{ $requisicao->parecer_aprovacao }}"</p>
                        @endif
                    @else
                        <span class="absolute -left-3 flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-gray-400 ring-8 ring-white dark:bg-gray-700 dark:ring-gray-800">
                            <span class="h-2 w-2 rounded-full bg-gray-400"></span>
                        </span>
                        <h3 class="text-xs font-medium text-gray-400">Aprovação da Chefia</h3>
                        <p class="text-xs text-amber-500">Aguardando parecer do gestor da unidade</p>
                    @endif
                </li>

                <!-- 3. Atendimento Almoxarifado -->
                <li class="ml-6">
                    @if($requisicao->atendido_em)
                        <span class="absolute -left-3 flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-blue-600 ring-8 ring-white dark:bg-blue-900 dark:text-blue-300 dark:ring-gray-800">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </span>
                        <h3 class="text-xs font-bold text-gray-900 dark:text-white">Atendido pelo Almoxarife</h3>
                        <p class="text-xs text-gray-500">{{ $requisicao->atendido_em->format('d/m/Y H:i') }}</p>
                        <p class="text-xs text-gray-400">Por {{ $requisicao->atendidoPor?->name }}</p>
                        @if($requisicao->observacoes_atendimento)
                        <p class="mt-1 text-xs italic text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-900 p-2 rounded">"{{ $requisicao->observacoes_atendimento }}"</p>
                        @endif
                    @else
                        <span class="absolute -left-3 flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-gray-400 ring-8 ring-white dark:bg-gray-700 dark:ring-gray-800">
                            <span class="h-2 w-2 rounded-full bg-gray-400"></span>
                        </span>
                        <h3 class="text-xs font-medium text-gray-400">Separação no Almoxarifado</h3>
                        <p class="text-xs text-gray-400">Aguardando atendimento</p>
                    @endif
                </li>

                <!-- 4. Recebimento Solicitante -->
                <li class="ml-6">
                    @if($requisicao->recebido_em)
                        <span class="absolute -left-3 flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 ring-8 ring-white dark:bg-emerald-900 dark:text-emerald-300 dark:ring-gray-800">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <h3 class="text-xs font-bold text-emerald-700 dark:text-white">Recebimento Confirmado</h3>
                        <p class="text-xs text-gray-500">{{ $requisicao->recebido_em->format('d/m/Y H:i') }}</p>
                        <p class="text-xs text-gray-400">Por {{ $requisicao->recebidoPor?->name }}</p>
                    @else
                        <span class="absolute -left-3 flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-gray-400 ring-8 ring-white dark:bg-gray-700 dark:ring-gray-800">
                            <span class="h-2 w-2 rounded-full bg-gray-400"></span>
                        </span>
                        <h3 class="text-xs font-medium text-gray-400">Confirmação de Recebimento</h3>
                        <p class="text-xs text-gray-400">Pendente de entrega/retirada</p>
                    @endif
                </li>
            </ol>
        </div>
    </div>

    <!-- Coluna da Direita: Grade de Materiais Solicitados e Atendidos -->
    <div class="lg:col-span-2 space-y-6">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4 flex items-center justify-between">
                <span class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    Itens da Requisição
                </span>
                <span class="text-xs font-normal text-gray-500 dark:text-gray-400">
                    Total: {{ $requisicao->itens->count() }} item(ns)
                </span>
            </h2>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                    <thead class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                        <tr>
                            <th class="px-4 py-3">Código / Material</th>
                            <th class="px-4 py-3 text-center">Unidade</th>
                            <th class="px-4 py-3 text-right">Qtd Solicitada</th>
                            <th class="px-4 py-3 text-right">Qtd Atendida</th>
                            <th class="px-4 py-3 text-right">Saldo Almoxarifado</th>
                            <th class="px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($requisicao->itens as $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="px-4 py-3.5">
                                <span class="font-bold text-gray-900 dark:text-white">{{ $item->produto?->nome }}</span>
                                <span class="block text-xs text-gray-400">SKU: {{ $item->produto?->codigo_sku ?: '—' }} | Loc: {{ $item->produto?->estoque_localizacao ?: 'Central' }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-center font-medium text-xs">
                                {{ $item->produto?->unidade ?: 'UN' }}
                            </td>
                            <td class="px-4 py-3.5 text-right font-bold text-gray-900 dark:text-white">
                                {{ number_format($item->quantidade_solicitada, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-right font-bold text-emerald-600 dark:text-emerald-400">
                                {{ number_format($item->quantidade_atendida, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-right text-xs">
                                <span class="font-semibold {{ (float) $item->produto?->estoque_quantidade <= (float) $item->produto?->estoque_minimo ? 'text-amber-600' : 'text-gray-700 dark:text-gray-300' }}">
                                    {{ number_format((float) $item->produto?->estoque_quantidade, 2, ',', '.') }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @if((float) $item->quantidade_atendida >= (float) $item->quantidade_solicitada && (float) $item->quantidade_atendida > 0)
                                <span class="inline-flex rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                    Atendido
                                </span>
                                @elseif((float) $item->quantidade_atendida > 0)
                                <span class="inline-flex rounded-full bg-cyan-100 px-2 py-0.5 text-xs font-semibold text-cyan-800 dark:bg-cyan-900/30 dark:text-cyan-400">
                                    Parcial
                                </span>
                                @else
                                <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                    Pendente
                                </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL APROVAR REQUISIÇÃO --}}
<div id="modalAprovar" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 hidden">
    <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-gray-800">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Aprovar Requisição de Material</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
            Como chefia/gestor da unidade, aprove esta requisição para que o Almoxarifado providencie a separação física dos materiais.
        </p>

        <form method="POST" action="{{ route('almoxarifado.requisicoes.aprovar', $requisicao) }}">
            @csrf
            <div class="mb-4">
                <label class="block text-xs font-semibold uppercase text-gray-700 dark:text-gray-300 mb-1">
                    Parecer / Despacho de Aprovação (Opcional)
                </label>
                <textarea name="parecer" rows="3" placeholder="Observações para o almoxarife..." class="w-full rounded-lg border border-gray-300 p-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modalAprovar').classList.add('hidden')" class="rounded-lg border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">
                    Cancelar
                </button>
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white hover:bg-blue-700">
                    Confirmar Aprovação
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL RECUSAR REQUISIÇÃO --}}
<div id="modalRecusar" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 hidden">
    <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-gray-800">
        <h3 class="text-lg font-bold text-red-600 dark:text-red-400 mb-2">Recusar Requisição</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
            Informe o motivo circunstanciado da recusa desta requisição. O solicitante será notificado.
        </p>

        <form method="POST" action="{{ route('almoxarifado.requisicoes.recusar', $requisicao) }}">
            @csrf
            <div class="mb-4">
                <label class="block text-xs font-semibold uppercase text-gray-700 dark:text-gray-300 mb-1">
                    Motivo da Recusa <span class="text-red-500">*</span>
                </label>
                <textarea name="motivo" required rows="3" placeholder="Justifique o motivo..." class="w-full rounded-lg border border-gray-300 p-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modalRecusar').classList.add('hidden')" class="rounded-lg border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">
                    Voltar
                </button>
                <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-xs font-semibold text-white hover:bg-red-700">
                    Confirmar Recusa
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL ATENDER E BAIXAR ESTOQUE --}}
<div id="modalAtender" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 hidden">
    <div class="w-full max-w-xl rounded-xl bg-white p-6 shadow-xl dark:bg-gray-800 max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Atendimento do Almoxarifado</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
            Confirme as quantidades a serem fornecidas. Ao confirmar, o sistema realizará a **baixa automática do saldo no estoque municipal** e registrará a saída no Kardex.
        </p>

        <form method="POST" action="{{ route('almoxarifado.requisicoes.atender', $requisicao) }}">
            @csrf

            <div class="mb-4 space-y-3">
                @foreach($requisicao->itens as $item)
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900">
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <span class="text-xs font-bold text-gray-900 dark:text-white">{{ $item->produto?->nome }}</span>
                            <span class="block text-[10px] text-gray-500">Saldo atual: {{ (float) $item->produto?->estoque_quantidade }} {{ $item->produto?->unidade ?: 'UN' }}</span>
                        </div>
                        <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">
                            Pedida: {{ (float) $item->quantidade_solicitada }} {{ $item->produto?->unidade ?: 'UN' }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <label class="text-xs text-gray-600 dark:text-gray-400 whitespace-nowrap">Qtd a Atender:</label>
                        <input type="number" step="0.01" min="0" max="{{ (float) $item->produto?->estoque_quantidade }}" name="itens[{{ $item->id }}]" value="{{ (float) $item->quantidade_solicitada }}" class="block w-full rounded-lg border border-gray-300 px-3 py-1 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white" required>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mb-4">
                <label class="block text-xs font-semibold uppercase text-gray-700 dark:text-gray-300 mb-1">
                    Observações de Separação / Guia de Saída (Opcional)
                </label>
                <textarea name="observacoes" rows="2" placeholder="Ex.: Lote nº 123, entregue ao motorista..." class="w-full rounded-lg border border-gray-300 p-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modalAtender').classList.add('hidden')" class="rounded-lg border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">
                    Cancelar
                </button>
                <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700">
                    Efetivar Baixa no Estoque
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
