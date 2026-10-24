{{--
 * Componente da aplicação Phoenix - SIGEM
 * Secretarias e setores (estrutura administrativa) — padrão SIGEM Clean
 *
 * @license AGPL-3.0
 --}}
@extends('layouts.app')

@section('content')
@php $tipos = ['gabinete' => 'Gabinete', 'secretaria' => 'Secretaria', 'departamento' => 'Departamento', 'divisao' => 'Divisão', 'setor' => 'Setor']; @endphp
@php $podeGerenciar = (bool) auth()->user()->is_admin; @endphp
<x-sigem.page-header title="Secretarias" subtitle="Estrutura administrativa usada no Protocolo, Almoxarifado, Frota, Zeladoria e na lotação dos servidores">
    <x-slot:actions>
        @if($podeGerenciar)
        <x-sigem.button variant="primary" href="{{ route('secretarias.create') }}">
            <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Nova secretaria ou setor
        </x-sigem.button>
        @endif
    </x-slot:actions>
</x-sigem.page-header>

<x-sigem.flash />

<div class="sigem-card">
    <form method="GET" action="{{ route('secretarias.index') }}" class="flex flex-wrap gap-2.5 p-3" x-data x-ref="f">
        <label for="s-busca" class="sr-only">Buscar</label>
        <input id="s-busca" type="search" name="busca" value="{{ request('busca') }}" placeholder="Nome, sigla ou e-mail" class="sigem-input min-w-0 flex-[1_1_260px]">
        <label for="s-tipo" class="sr-only">Tipo</label>
        <select id="s-tipo" name="tipo" class="sigem-input flex-[0_1_180px]" @change="$refs.f.submit()">
            <option value="">Qualquer tipo</option>
            @foreach($tipos as $v => $r)<option value="{{ $v }}" @selected(request('tipo') === $v)>{{ $r }}</option>@endforeach
        </select>
        <label for="s-ativo" class="sr-only">Situação</label>
        <select id="s-ativo" name="ativo" class="sigem-input flex-[0_1_160px]" @change="$refs.f.submit()">
            <option value="">Ativas e inativas</option>
            <option value="1" @selected(request('ativo') === '1')>Somente ativas</option>
            <option value="0" @selected(request('ativo') === '0')>Somente inativas</option>
        </select>
        <x-sigem.button type="submit">Buscar</x-sigem.button>
    </form>

    @if($unidades->isEmpty())
        <x-sigem.empty title="Nenhuma unidade encontrada" description="Cadastre as secretarias para poder lotar servidores e tramitar processos." />
    @else
        <x-sigem.table min-width="760px">
            <x-slot:head>
                <th scope="col">Unidade</th>
                <th scope="col">Tipo</th>
                <th scope="col">Responsável</th>
                <th scope="col" class="text-right">Servidores</th>
                <th scope="col" class="text-right">Processos</th>
                <th scope="col">Situação</th>
                <th scope="col"><span class="sr-only">Ações</span></th>
            </x-slot:head>
            @foreach($unidades as $u)
                <tr>
                    <td>
                        @if($podeGerenciar)
                            <a href="{{ route('secretarias.edit', $u) }}" class="sigem-link">{{ $u->sigla ? $u->sigla . ' — ' : '' }}{{ $u->nome }}</a>
                        @else
                            <strong>{{ $u->sigla ? $u->sigla . ' — ' : '' }}{{ $u->nome }}</strong>
                        @endif
                        @if($u->parent)<div class="sigem-muted">Subordinada a {{ $u->parent->sigla ?: $u->parent->nome }}</div>@endif
                    </td>
                    <td>{{ $tipos[$u->tipo] ?? ucfirst($u->tipo) }}</td>
                    <td>{{ $u->responsavel?->name ?? '—' }}</td>
                    <td class="text-right">
                        @if($u->servidores_count)
                            {{ $u->servidores_count }}
                        @else
                            <span class="text-sigem-warn dark:text-amber-300" title="Sem servidores lotados: ninguém consegue receber processos aqui">0</span>
                        @endif
                    </td>
                    <td class="text-right">{{ $u->processos_atuais_count }}</td>
                    <td><x-sigem.status :tom="$u->ativo ? 'ok' : 'neutral'">{{ $u->ativo ? 'Ativa' : 'Inativa' }}</x-sigem.status></td>
                    <td class="text-right">
                        @if($podeGerenciar)
                            <x-sigem.button size="sm" href="{{ route('secretarias.edit', $u) }}">Editar</x-sigem.button>
                        @endif
                    </td>
                </tr>
            @endforeach
        </x-sigem.table>
        @if($unidades->hasPages())
            <div class="border-t border-sigem-line px-4 py-3 dark:border-gray-800">{{ $unidades->links() }}</div>
        @endif
    @endif
</div>
@endsection
