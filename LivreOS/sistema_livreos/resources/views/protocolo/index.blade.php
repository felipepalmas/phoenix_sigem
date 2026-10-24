{{--
 * Componente da aplicação Phoenix - SIGEM (Zero Papel)
 * Protocolo — lista de processos (padrão SIGEM Clean)
 *
 * @license AGPL-3.0
 --}}
@extends('layouts.app')

@section('content')
<x-sigem.page-header title="Protocolo" subtitle="Processos administrativos digitais">
    <x-slot:actions>
        <x-sigem.button variant="primary" href="{{ route('protocolo.create') }}">
            <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Autuar processo
        </x-sigem.button>
    </x-slot:actions>
</x-sigem.page-header>

<x-sigem.flash />

<div class="sigem-card">
    <x-sigem.tabs :items="$abas" label="Recorte de processos" class="px-3" />

    <form method="GET" action="{{ route('protocolo.index') }}" class="flex flex-wrap gap-2.5 p-3" x-data x-ref="filtros">
        <input type="hidden" name="recorte" value="{{ $recorte }}">
        <label for="f-busca" class="sr-only">Buscar por número, assunto ou interessado</label>
        <input id="f-busca" type="search" name="busca" value="{{ request('busca') }}" placeholder="Número, assunto, interessado ou CPF/CNPJ" class="sigem-input min-w-0 flex-[1_1_280px]">
        <label for="f-status" class="sr-only">Situação</label>
        <select id="f-status" name="status" class="sigem-input flex-[0_1_190px]" @change="$refs.filtros.submit()">
            <option value="">Qualquer situação</option>
            @foreach(['tramitando' => 'Em tramitação', 'suspenso' => 'Suspenso', 'deferido' => 'Deferido', 'indeferido' => 'Indeferido', 'arquivado' => 'Arquivado'] as $valor => $rotulo)
                <option value="{{ $valor }}" @selected(request('status') === $valor)>{{ $rotulo }}</option>
            @endforeach
        </select>
        <label for="f-unidade" class="sr-only">Secretaria atual</label>
        <select id="f-unidade" name="unidade_id" class="sigem-input flex-[0_1_210px]" @change="$refs.filtros.submit()">
            <option value="">Qualquer secretaria</option>
            @foreach($unidades as $u)
                <option value="{{ $u->id }}" @selected((string) request('unidade_id') === (string) $u->id)>{{ $u->sigla ? $u->sigla . ' — ' : '' }}{{ $u->nome }}</option>
            @endforeach
        </select>
        <label for="f-prioridade" class="sr-only">Prioridade</label>
        <select id="f-prioridade" name="prioridade" class="sigem-input flex-[0_1_170px]" @change="$refs.filtros.submit()">
            <option value="">Qualquer prioridade</option>
            <option value="urgente" @selected(request('prioridade') === 'urgente')>Urgente</option>
            <option value="normal" @selected(request('prioridade') === 'normal')>Normal</option>
            <option value="baixa" @selected(request('prioridade') === 'baixa')>Baixa</option>
        </select>
        <x-sigem.button type="submit">Buscar</x-sigem.button>
        @if($filtrosAtivos)
            <x-sigem.button variant="ghost" href="{{ route('protocolo.index', ['recorte' => $recorte]) }}">Limpar filtros</x-sigem.button>
        @endif
    </form>

    @if($processos->isEmpty())
        <x-sigem.empty title="Nenhum processo neste recorte"
            :description="$filtrosAtivos ? 'Nenhum processo corresponde aos filtros. Tente limpar a busca.' : 'Quando um processo for autuado ou enviado para cá, ele aparece nesta lista.'">
            @unless($filtrosAtivos)<x-sigem.button variant="primary" href="{{ route('protocolo.create') }}">Autuar processo</x-sigem.button>@endunless
        </x-sigem.empty>
    @else
        <x-sigem.table min-width="820px">
            <x-slot:head>
                <th scope="col">Número</th>
                <th scope="col">Assunto</th>
                <th scope="col">Onde está</th>
                <th scope="col">Prazo</th>
                <th scope="col">Situação</th>
            </x-slot:head>
            @foreach($processos as $processo)
                @php
                    $aReceber = $processo->tramitacoes->contains(fn ($t) => (int) $t->unidade_destino_id === (int) $processo->unidade_atual_id) && ! $processo->estaEncerrado();
                    $dias = $processo->dias_para_prazo;
                @endphp
                <tr>
                    <td class="whitespace-nowrap">
                        <a href="{{ route('protocolo.show', $processo) }}" class="sigem-link">{{ $processo->numero }}</a>
                        <div class="flex gap-1.5 text-xs font-semibold">
                            @if($processo->prioridade === 'urgente')<span class="text-sigem-danger dark:text-red-400">Urgente</span>@endif
                            @if($processo->sigilo !== 'publico')<span class="text-sigem-muted">{{ $processo->sigilo_rotulo }}</span>@endif
                        </div>
                    </td>
                    <td class="max-w-[420px]">
                        <div class="truncate" title="{{ $processo->assunto }}">{{ $processo->assunto }}</div>
                        <div class="truncate text-[13px] text-sigem-muted dark:text-gray-400">{{ $processo->interessado?->nome ?: ($processo->interessado?->razao_social ?: 'Interessado não informado') }}</div>
                    </td>
                    <td>
                        <span title="{{ $processo->unidadeAtual?->nome }}">{{ $processo->unidadeAtual?->sigla ?: ($processo->unidadeAtual?->nome ?? '—') }}</span>
                        <div class="text-[13px] text-sigem-muted dark:text-gray-400">
                            @if($processo->estaEncerrado())
                                {{ $processo->data_conclusao ? 'encerrado em ' . $processo->data_conclusao->format('d/m/Y') : 'encerrado' }}
                            @elseif($aReceber)
                                a receber
                            @elseif($processo->usuarioResponsavel)
                                com {{ explode(' ', $processo->usuarioResponsavel->name)[0] }}
                            @else
                                aguardando atribuição
                            @endif
                        </div>
                    </td>
                    <td class="whitespace-nowrap">
                        @if($dias === null)
                            <span class="text-sigem-muted">{{ $processo->data_limite && !$processo->estaEncerrado() ? $processo->data_limite->format('d/m') : '—' }}</span>
                        @elseif($dias < 0)
                            <x-sigem.status tom="danger">Vencido há {{ abs($dias) }} {{ abs($dias) === 1 ? 'dia' : 'dias' }}</x-sigem.status>
                        @elseif($dias <= 3)
                            <x-sigem.status tom="warn">{{ $processo->data_limite->format('d/m') }} · {{ $dias === 0 ? 'hoje' : $dias . ($dias === 1 ? ' dia' : ' dias') }}</x-sigem.status>
                        @else
                            {{ $processo->data_limite->format('d/m') }}
                        @endif
                    </td>
                    <td><x-sigem.status :tom="$processo->status_tom">{{ $processo->status_rotulo }}</x-sigem.status></td>
                </tr>
            @endforeach
        </x-sigem.table>
        <div class="flex flex-wrap items-center gap-2 border-t border-sigem-line px-4 py-3 text-[13px] text-sigem-muted dark:border-gray-800 dark:text-gray-400">
            <span class="flex-1">Mostrando {{ $processos->firstItem() }}–{{ $processos->lastItem() }} de {{ $processos->total() }} {{ $processos->total() === 1 ? 'processo' : 'processos' }}</span>
            @if($processos->hasPages())
                <x-sigem.button size="sm" :href="$processos->previousPageUrl()" :class="$processos->onFirstPage() ? 'pointer-events-none opacity-50' : ''">Anterior</x-sigem.button>
                <x-sigem.button size="sm" :href="$processos->nextPageUrl()" :class="$processos->hasMorePages() ? '' : 'pointer-events-none opacity-50'">Próxima</x-sigem.button>
            @endif
        </div>
    @endif
</div>
@endsection
