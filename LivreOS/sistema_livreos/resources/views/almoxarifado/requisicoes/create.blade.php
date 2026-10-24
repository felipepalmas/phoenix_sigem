{{--
 * Componente da aplicação Phoenix - SIGEM (Zero Papel)
 * Almoxarifado — nova requisição de materiais (padrão SIGEM Clean)
 *
 * @license AGPL-3.0
 --}}
@extends('layouts.app')

@section('content')
@php
    $lotacao = auth()->user()->unidade_administrativa_id;
    $catalogo = $materiais->mapWithKeys(fn ($m) => [$m->id => [
        'nome' => $m->nome,
        'sku' => $m->codigo_sku,
        'unidade' => $m->unidade ?: 'UN',
        'estoque' => (float) $m->estoque_quantidade,
        'minimo' => (float) $m->estoque_minimo,
    ]]);
    $linhasIniciais = collect(old('itens', [['produto_id' => '', 'quantidade' => '']]))
        ->values()
        ->map(fn ($l, $i) => ['id' => $i + 1, 'produto_id' => (string) ($l['produto_id'] ?? ''), 'quantidade' => (string) ($l['quantidade'] ?? '')]);
@endphp
<x-sigem.page-header title="Nova requisição de materiais" subtitle="O pedido vai para aprovação da chefia da secretaria e, depois, para o almoxarifado separar."
    :breadcrumbs="['Requisições' => route('almoxarifado.requisicoes.index'), 'Nova requisição' => null]" />

<x-sigem.flash />

<form method="POST" action="{{ route('almoxarifado.requisicoes.store') }}" class="flex max-w-4xl flex-col gap-5">
    @csrf

    <x-sigem.card title="Quem pede e de onde sai" id="t-origem">
        <div class="grid gap-4 md:grid-cols-2">
            <x-sigem.field label="Secretaria solicitante" for="unidade_solicitante_id" required help="Sua lotação já vem selecionada. A chefia desta secretaria aprova o pedido.">
                <select id="unidade_solicitante_id" name="unidade_solicitante_id" required class="sigem-input">
                    <option value="">Selecione a secretaria</option>
                    @foreach($unidades as $u)
                        <option value="{{ $u->id }}" @selected((string) old('unidade_solicitante_id', $lotacao) === (string) $u->id)>{{ $u->sigla ? $u->sigla . ' — ' : '' }}{{ $u->nome }}</option>
                    @endforeach
                </select>
            </x-sigem.field>
            <x-sigem.field label="Almoxarifado" for="almoxarifado_id" required help="De onde os materiais serão retirados.">
                <select id="almoxarifado_id" name="almoxarifado_id" required class="sigem-input">
                    <option value="">Selecione o almoxarifado</option>
                    @foreach($almoxarifados as $a)
                        <option value="{{ $a->id }}" @selected((string) old('almoxarifado_id') === (string) $a->id)>{{ $a->nome }}</option>
                    @endforeach
                </select>
            </x-sigem.field>
            <x-sigem.field label="Prioridade" for="prioridade" required>
                <select id="prioridade" name="prioridade" required class="sigem-input">
                    <option value="normal" @selected(old('prioridade', 'normal') === 'normal')>Normal</option>
                    <option value="urgente" @selected(old('prioridade') === 'urgente')>Urgente</option>
                </select>
            </x-sigem.field>
            <x-sigem.field label="Processo vinculado" for="processo_id" help="Opcional. Liga a requisição a um processo do Protocolo.">
                <select id="processo_id" name="processo_id" class="sigem-input">
                    <option value="">Nenhum processo</option>
                    @foreach($processos as $proc)
                        <option value="{{ $proc->id }}" @selected((string) old('processo_id') === (string) $proc->id)>{{ $proc->numero }} — {{ \Illuminate\Support\Str::limit($proc->assunto, 50) }}</option>
                    @endforeach
                </select>
            </x-sigem.field>
            <x-sigem.field label="Justificativa" for="justificativa" required class="md:col-span-2" help="Para que os materiais serão usados e onde. Mínimo de 5 caracteres.">
                <textarea id="justificativa" name="justificativa" rows="3" required minlength="5" class="sigem-input" placeholder="Ex.: Reposição de material de limpeza da Escola Municipal Castro Alves">{{ old('justificativa') }}</textarea>
            </x-sigem.field>
        </div>
    </x-sigem.card>

    <div x-data="{
            catalogo: @js($catalogo),
            linhas: @js($linhasIniciais),
            seq: {{ $linhasIniciais->count() }},
            adicionar() { this.seq++; this.linhas.push({ id: this.seq, produto_id: '', quantidade: '' }); this.$nextTick(() => document.getElementById('item-produto-' + this.seq)?.focus()); },
            remover(i) { this.linhas.splice(i, 1); },
            mat(l) { return this.catalogo[l.produto_id] || null; },
            num(v) { return Number(v).toLocaleString('pt-BR', { maximumFractionDigits: 4 }); },
            falta(l) { const m = this.mat(l); return m && l.quantidade !== '' && Number(l.quantidade) > m.estoque; }
        }">
        <x-sigem.card title="Materiais" id="t-itens" subtitle="Escolha do catálogo e informe a quantidade. O saldo mostrado é o estoque atual.">
            <x-slot:actions>
                <x-sigem.button size="sm" @click="adicionar()">
                    <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                    Adicionar material
                </x-sigem.button>
            </x-slot:actions>

            <template x-if="linhas.length === 0">
                <x-sigem.empty title="Nenhum material na lista" description="Adicione pelo menos um material para enviar a requisição." />
            </template>

            <ul class="m-0 flex list-none flex-col p-0">
                <template x-for="(l, i) in linhas" :key="l.id">
                    <li class="flex flex-wrap items-start gap-3 border-b border-gray-100 py-3 first:pt-0 last:border-b-0 last:pb-0 dark:border-gray-800">
                        <div class="min-w-0 flex-[1_1_320px]">
                            <label :for="'item-produto-' + l.id" class="sigem-label" x-text="'Material ' + (i + 1)"></label>
                            <select :id="'item-produto-' + l.id" :name="'itens[' + i + '][produto_id]'" x-model="l.produto_id" required class="sigem-input">
                                <option value="">Selecione o material</option>
                                @foreach($materiais as $m)
                                    <option value="{{ $m->id }}">{{ $m->nome }}{{ $m->codigo_sku ? ' — ' . $m->codigo_sku : '' }}</option>
                                @endforeach
                            </select>
                            <p class="sigem-help" x-show="mat(l)">
                                <span x-text="'Unidade: ' + (mat(l)?.unidade ?? '')"></span>
                                <span aria-hidden="true"> · </span>
                                <span x-text="'Saldo atual: ' + num(mat(l)?.estoque ?? 0) + ' ' + (mat(l)?.unidade ?? '')"></span>
                                <template x-if="mat(l) && mat(l).estoque <= 0">
                                    <span class="sigem-badge sigem-badge-danger ml-1">Sem estoque</span>
                                </template>
                                <template x-if="mat(l) && mat(l).estoque > 0 && mat(l).minimo > 0 && mat(l).estoque <= mat(l).minimo">
                                    <span class="sigem-badge sigem-badge-warn ml-1">Estoque mínimo</span>
                                </template>
                            </p>
                        </div>
                        <div class="w-40 shrink-0">
                            <label :for="'item-qtd-' + l.id" class="sigem-label">Quantidade</label>
                            <div class="flex items-center gap-2">
                                <input :id="'item-qtd-' + l.id" :name="'itens[' + i + '][quantidade]'" x-model="l.quantidade" type="number" step="any" min="0.01" required inputmode="decimal" class="sigem-input">
                                <span class="w-8 text-[13px] text-sigem-muted dark:text-gray-400" x-text="mat(l)?.unidade ?? ''"></span>
                            </div>
                            <p class="sigem-help text-sigem-warn dark:text-amber-300" x-show="falta(l)">Acima do saldo; pode ser atendida em parte.</p>
                        </div>
                        <div class="shrink-0 pt-6">
                            <button type="button" class="sigem-btn sigem-btn-ghost sigem-btn-sm px-2" @click="remover(i)" :aria-label="'Remover material ' + (i + 1)" :disabled="linhas.length === 1">
                                <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 7h16M10 11v6M14 11v6M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2l1-12M9 7V4h6v3"/></svg>
                            </button>
                        </div>
                    </li>
                </template>
            </ul>
        </x-sigem.card>
    </div>

    <div class="flex flex-wrap justify-end gap-2">
        <x-sigem.button href="{{ route('almoxarifado.requisicoes.index') }}">Cancelar</x-sigem.button>
        <x-sigem.button type="submit" variant="primary">Enviar para aprovação</x-sigem.button>
    </div>
</form>
@endsection
