{{--
 * Componente da aplicação Phoenix - SIGEM (Zero Papel)
 * Almoxarifado — Kardex de estoque e registro de entradas (padrão SIGEM Clean)
 *
 * @license AGPL-3.0
 --}}
@extends('layouts.app')

@section('content')
@php
    $qtd = fn ($v) => rtrim(rtrim(number_format((float) $v, 4, ',', '.'), '0'), ',');
    $tipos = ['entrada' => 'Entrada', 'saida' => 'Saída', 'ajuste' => 'Ajuste de inventário', 'transferencia' => 'Transferência'];
    $origens = ['requisicao' => 'Requisição', 'ordem_servico' => 'Ordem de serviço', 'nota_empenho' => 'Empenho / nota fiscal', 'inventario' => 'Inventário', 'avulso' => 'Avulsa'];
@endphp

<div x-data="{ painel: @js(old('_painel', '')) }">
<x-sigem.page-header title="Kardex de estoque" subtitle="Toda entrada e saída de material, com saldo antes e depois e o documento de origem">
    @if($podeRegistrar)
        <x-slot:actions>
            <x-sigem.button variant="primary" @click="painel = painel === 'entrada' ? '' : 'entrada'" ::aria-expanded="painel === 'entrada'">
                <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                Registrar entrada
            </x-sigem.button>
        </x-slot:actions>
    @endif
</x-sigem.page-header>

<x-sigem.flash />

@if($podeRegistrar)
<form x-show="painel === 'entrada'" x-cloak method="POST" action="{{ route('almoxarifado.movimentacoes.entrada') }}" class="sigem-card mb-5 border-sigem-primary"
    x-data="{ produtos: @js($produtos->mapWithKeys(fn ($p) => [$p->id => ['unidade' => $p->unidade ?: 'UN', 'estoque' => (float) $p->estoque_quantidade]])), produto: @js((string) old('produto_id', '')) }">
    @csrf
    <input type="hidden" name="_painel" value="entrada">
    <div class="sigem-card-header">
        <div class="min-w-0 flex-1">
            <h2 class="sigem-card-title">Registrar entrada de material</h2>
            <p class="m-0 mt-0.5 text-[13px] text-sigem-muted dark:text-gray-400">Recebimento de fornecedor por nota de empenho ou nota fiscal. O saldo do material aumenta na hora.</p>
        </div>
    </div>
    <div class="sigem-card-body flex flex-col gap-4">
        <div class="grid gap-4 md:grid-cols-2">
            <x-sigem.field label="Material" for="produto_id" required>
                <select id="produto_id" name="produto_id" required class="sigem-input" x-model="produto">
                    <option value="">Selecione o material</option>
                    @foreach($produtos as $p)
                        <option value="{{ $p->id }}">{{ $p->nome }}{{ $p->codigo_sku ? ' — ' . $p->codigo_sku : '' }}</option>
                    @endforeach
                </select>
                <p class="sigem-help" x-show="produtos[produto]" x-text="produtos[produto] ? 'Saldo atual: ' + Number(produtos[produto].estoque).toLocaleString('pt-BR', { maximumFractionDigits: 4 }) + ' ' + produtos[produto].unidade : ''"></p>
            </x-sigem.field>
            <x-sigem.field label="Almoxarifado de destino" for="deposito_id" required help="Somente almoxarifados da sua secretaria (ou todos, para gestores).">
                <select id="deposito_id" name="deposito_id" required class="sigem-input">
                    <option value="">Selecione o almoxarifado</option>
                    @foreach($depositosEntrada as $d)
                        <option value="{{ $d->id }}" @selected((string) old('deposito_id') === (string) $d->id)>{{ $d->nome }}</option>
                    @endforeach
                </select>
            </x-sigem.field>
            <x-sigem.field label="Quantidade recebida" for="quantidade" required>
                <div class="flex items-center gap-2">
                    <input id="quantidade" name="quantidade" type="number" step="any" min="0.01" required inputmode="decimal" value="{{ old('quantidade') }}" class="sigem-input">
                    <span class="w-8 text-[13px] text-sigem-muted dark:text-gray-400" x-text="produtos[produto]?.unidade ?? ''"></span>
                </div>
            </x-sigem.field>
            <x-sigem.field label="Documento" for="documento_ref" required help="Número da nota de empenho ou da nota fiscal.">
                <input id="documento_ref" name="documento_ref" type="text" required maxlength="100" value="{{ old('documento_ref') }}" class="sigem-input" placeholder="Ex.: Empenho 450/2026">
            </x-sigem.field>
        </div>
        <x-sigem.field label="Observações" for="justificativa" help="Opcional. Fornecedor, lote, validade.">
            <textarea id="justificativa" name="justificativa" rows="2" maxlength="500" class="sigem-input">{{ old('justificativa') }}</textarea>
        </x-sigem.field>
        <div class="flex justify-end gap-2">
            <x-sigem.button @click="painel = ''">Cancelar</x-sigem.button>
            <x-sigem.button type="submit" variant="primary">Registrar entrada</x-sigem.button>
        </div>
    </div>
</form>
@endif

<div class="sigem-card">
    <form method="GET" action="{{ route('almoxarifado.movimentacoes.index') }}" class="flex flex-wrap gap-2.5 p-3" x-data x-ref="filtros">
        <label for="k-busca" class="sr-only">Buscar por documento, justificativa ou material</label>
        <input id="k-busca" type="search" name="busca" value="{{ request('busca') }}" placeholder="Documento, material ou código" class="sigem-input min-w-0 flex-[1_1_240px]">
        <label for="k-produto" class="sr-only">Material</label>
        <select id="k-produto" name="produto_id" class="sigem-input flex-[0_1_240px]" @change="$refs.filtros.submit()">
            <option value="">Qualquer material</option>
            @foreach($produtos as $p)
                <option value="{{ $p->id }}" @selected((string) request('produto_id') === (string) $p->id)>{{ $p->nome }}</option>
            @endforeach
        </select>
        <label for="k-deposito" class="sr-only">Almoxarifado</label>
        <select id="k-deposito" name="deposito_id" class="sigem-input flex-[0_1_220px]" @change="$refs.filtros.submit()">
            <option value="">Qualquer almoxarifado</option>
            @foreach($almoxarifados as $a)
                <option value="{{ $a->id }}" @selected((string) request('deposito_id') === (string) $a->id)>{{ $a->nome }}</option>
            @endforeach
        </select>
        <label for="k-tipo" class="sr-only">Tipo de movimentação</label>
        <select id="k-tipo" name="tipo" class="sigem-input flex-[0_1_180px]" @change="$refs.filtros.submit()">
            <option value="">Qualquer tipo</option>
            @foreach($tipos as $valor => $rotulo)
                <option value="{{ $valor }}" @selected(request('tipo') === $valor)>{{ $rotulo }}</option>
            @endforeach
        </select>
        <x-sigem.button type="submit">Buscar</x-sigem.button>
        @if($filtrosAtivos)
            <x-sigem.button variant="ghost" href="{{ route('almoxarifado.movimentacoes.index') }}">Limpar filtros</x-sigem.button>
        @endif
    </form>

    @if($movimentacoes->isEmpty())
        <x-sigem.empty title="Nenhuma movimentação encontrada"
            :description="$filtrosAtivos ? 'Nenhuma movimentação corresponde aos filtros. Tente limpar a busca.' : 'Entradas por empenho e saídas por requisição aparecem aqui assim que forem registradas.'" />
    @else
        <x-sigem.table min-width="960px">
            <x-slot:head>
                <th scope="col">Data</th>
                <th scope="col">Material</th>
                <th scope="col">Movimento</th>
                <th scope="col" class="text-right">Quantidade</th>
                <th scope="col" class="text-right">Saldo</th>
                <th scope="col">Documento</th>
                <th scope="col">Responsável</th>
            </x-slot:head>
            @foreach($movimentacoes as $mov)
                @php
                    $un = $mov->produto?->unidade ?: 'UN';
                    $sinal = $mov->tipo === 'entrada' ? '+' : ($mov->tipo === 'ajuste' ? '=' : '−');
                @endphp
                <tr>
                    <td class="whitespace-nowrap">
                        {{ $mov->created_at->format('d/m/Y') }}
                        <div class="text-[13px] text-sigem-muted dark:text-gray-400">{{ $mov->created_at->format('H:i') }}</div>
                    </td>
                    <td>
                        <div class="max-w-[240px] truncate font-semibold" title="{{ $mov->produto?->nome }}">{{ $mov->produto?->nome ?? 'Material removido' }}</div>
                        <div class="max-w-[240px] truncate text-[13px] text-sigem-muted dark:text-gray-400" title="{{ $mov->deposito?->nome }}">{{ $mov->deposito?->nome ?? '—' }}</div>
                    </td>
                    <td class="whitespace-nowrap">
                        {{ $tipos[$mov->tipo] ?? ucfirst($mov->tipo) }}
                        <div class="text-[13px] text-sigem-muted dark:text-gray-400">{{ $origens[$mov->origem_tipo] ?? ucfirst(str_replace('_', ' ', (string) $mov->origem_tipo)) }}</div>
                    </td>
                    <td class="whitespace-nowrap text-right font-semibold {{ $mov->tipo === 'entrada' ? 'text-sigem-ok dark:text-green-400' : '' }}">
                        {{ $sinal }}{{ $qtd($mov->quantidade) }} {{ $un }}
                    </td>
                    <td class="whitespace-nowrap text-right">
                        <span class="text-sigem-muted dark:text-gray-400">{{ $qtd($mov->saldo_anterior) }} →</span> <strong>{{ $qtd($mov->saldo_posterior) }}</strong>
                    </td>
                    <td>
                        @if($mov->origem_tipo === 'requisicao' && $mov->origem_id)
                            <a href="{{ route('almoxarifado.requisicoes.show', $mov->origem_id) }}" class="sigem-link">{{ $mov->documento_ref ?: 'Requisição' }}</a>
                        @else
                            <div class="max-w-[200px] truncate">{{ $mov->documento_ref ?: '—' }}</div>
                        @endif
                        @if($mov->justificativa)
                            <div class="max-w-[200px] truncate text-[13px] text-sigem-muted dark:text-gray-400" title="{{ $mov->justificativa }}">{{ $mov->justificativa }}</div>
                        @endif
                    </td>
                    <td class="whitespace-nowrap">{{ $mov->user?->name ?? 'Sistema' }}</td>
                </tr>
            @endforeach
        </x-sigem.table>
        <div class="flex flex-wrap items-center gap-2 border-t border-sigem-line px-4 py-3 text-[13px] text-sigem-muted dark:border-gray-800 dark:text-gray-400">
            <span class="flex-1">Mostrando {{ $movimentacoes->firstItem() }}–{{ $movimentacoes->lastItem() }} de {{ $movimentacoes->total() }} {{ $movimentacoes->total() === 1 ? 'movimentação' : 'movimentações' }}</span>
            @if($movimentacoes->hasPages())
                <x-sigem.button size="sm" :href="$movimentacoes->previousPageUrl()" :class="$movimentacoes->onFirstPage() ? 'pointer-events-none opacity-50' : ''">Anterior</x-sigem.button>
                <x-sigem.button size="sm" :href="$movimentacoes->nextPageUrl()" :class="$movimentacoes->hasMorePages() ? '' : 'pointer-events-none opacity-50'">Próxima</x-sigem.button>
            @endif
        </div>
    @endif
</div>
</div>
@endsection
