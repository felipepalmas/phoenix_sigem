{{--
 * Componente da aplicação Phoenix - SIGEM
 * Almoxarifado — locais de estoque (padrão SIGEM Clean)
 *
 * @author    viniciusvams
 * @copyright 2024-2026 LivreOS
 * @license   https://www.gnu.org/licenses/agpl-3.0.txt AGPL-3.0
 --}}
@extends('layouts.app')

@section('content')
<x-sigem.page-header title="Almoxarifados" subtitle="Locais de estoque e distribuição de materiais, cada um ligado à secretaria que o administra">
    <x-slot:actions>
        <x-sigem.button variant="primary" href="{{ route('depositos.create') }}">
            <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Novo almoxarifado
        </x-sigem.button>
    </x-slot:actions>
</x-sigem.page-header>

<x-sigem.flash />

@if(function_exists('do_action'))
<?php do_action('entity.index.before_table', 'deposito'); ?>
@endif

<div class="sigem-card">
    <form method="GET" action="{{ route('depositos.index') }}" class="flex flex-wrap gap-2.5 p-3">
        <label for="d-nome" class="sr-only">Buscar por nome</label>
        <input id="d-nome" type="search" name="nome" value="{{ request('nome') }}" placeholder="Nome do almoxarifado" class="sigem-input min-w-0 flex-[1_1_280px]">
        <x-sigem.button type="submit">Buscar</x-sigem.button>
        @if(request()->filled('nome'))
            <x-sigem.button variant="ghost" href="{{ route('depositos.index') }}">Limpar</x-sigem.button>
        @endif
    </form>

    @if($depositos->isEmpty())
        <x-sigem.empty title="Nenhum almoxarifado encontrado"
            :description="request()->filled('nome') ? 'Nenhum almoxarifado corresponde à busca.' : 'Cadastre o primeiro local de estoque para receber entradas e atender requisições.'">
            @unless(request()->filled('nome'))<x-sigem.button variant="primary" href="{{ route('depositos.create') }}">Novo almoxarifado</x-sigem.button>@endunless
        </x-sigem.empty>
    @else
        <x-sigem.table min-width="760px">
            <x-slot:head>
                <th scope="col">Almoxarifado</th>
                <th scope="col">Localização</th>
                <th scope="col">Secretaria responsável</th>
                <th scope="col">Situação</th>
                <th scope="col"><span class="sr-only">Ações</span></th>
            </x-slot:head>
            @foreach($depositos as $deposito)
                <tr>
                    <td>
                        <a href="{{ route('depositos.edit', $deposito) }}" class="sigem-link">{{ $deposito->nome }}</a>
                        @if($deposito->codigo)<div class="text-[13px] text-sigem-muted dark:text-gray-400">Código {{ $deposito->codigo }}</div>@endif
                    </td>
                    <td><div class="max-w-[260px] truncate" title="{{ $deposito->localizacao }}">{{ $deposito->localizacao ?: '—' }}</div></td>
                    <td>
                        @if($deposito->unidadeAdministrativa)
                            <span title="{{ $deposito->unidadeAdministrativa->nome }}">{{ $deposito->unidadeAdministrativa->sigla ?: $deposito->unidadeAdministrativa->nome }}</span>
                        @else
                            <span class="text-sigem-muted dark:text-gray-400">Geral (sem secretaria)</span>
                        @endif
                    </td>
                    <td><x-sigem.status :tom="$deposito->ativo ? 'ok' : 'neutral'">{{ $deposito->ativo ? 'Ativo' : 'Inativo' }}</x-sigem.status></td>
                    <td class="whitespace-nowrap text-right">
                        <div class="inline-flex gap-1.5">
                            <x-sigem.button size="sm" variant="ghost" href="{{ route('depositos.edit', $deposito) }}" aria-label="Editar {{ $deposito->nome }}">Editar</x-sigem.button>
                            <form action="{{ route('depositos.destroy', $deposito) }}" method="POST" onsubmit="return confirm('Excluir o almoxarifado {{ addslashes($deposito->nome) }}? Esta ação não pode ser desfeita.')">
                                @csrf
                                @method('DELETE')
                                <x-sigem.button size="sm" variant="danger" type="submit" aria-label="Excluir {{ $deposito->nome }}">Excluir</x-sigem.button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-sigem.table>
        <div class="flex flex-wrap items-center gap-2 border-t border-sigem-line px-4 py-3 text-[13px] text-sigem-muted dark:border-gray-800 dark:text-gray-400">
            <span class="flex-1">Mostrando {{ $depositos->firstItem() }}–{{ $depositos->lastItem() }} de {{ $depositos->total() }} {{ $depositos->total() === 1 ? 'almoxarifado' : 'almoxarifados' }}</span>
            @if($depositos->hasPages())
                <x-sigem.button size="sm" :href="$depositos->appends(request()->query())->previousPageUrl()" :class="$depositos->onFirstPage() ? 'pointer-events-none opacity-50' : ''">Anterior</x-sigem.button>
                <x-sigem.button size="sm" :href="$depositos->appends(request()->query())->nextPageUrl()" :class="$depositos->hasMorePages() ? '' : 'pointer-events-none opacity-50'">Próxima</x-sigem.button>
            @endif
        </div>
    @endif
</div>

@if(function_exists('do_action'))
<?php do_action('entity.index.after_table', 'deposito'); ?>
@endif
@endsection
