{{--
 * Componente da aplicação Phoenix - SIGEM
 * Frota — motoristas oficiais e validade da CNH (padrão SIGEM Clean)
 *
 * @license AGPL-3.0
 --}}
@extends('layouts.app')

@section('content')
@php
    $painelInicial = old('_painel', '');
@endphp

<div x-data="{ painel: @js($painelInicial) }">
<x-sigem.page-header title="Motoristas" subtitle="Servidores credenciados para conduzir veículos da frota e validade da CNH">
    @if($podeGerenciar)
        <x-slot:actions>
            <x-sigem.button variant="primary" @click="painel = painel === 'motorista' ? '' : 'motorista'" ::aria-expanded="painel === 'motorista'">
                <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                Credenciar motorista
            </x-sigem.button>
        </x-slot:actions>
    @endif
</x-sigem.page-header>

<x-sigem.flash />

@include('frota.partials.alerta-cnh', ['alertasCnh' => $alertasCnh, 'linkMotoristas' => false])

@if($podeGerenciar)
<form x-show="painel === 'motorista'" x-cloak method="POST" action="{{ route('frota.motoristas.store') }}" class="sigem-card mb-5 border-sigem-primary">
    @csrf
    <input type="hidden" name="_painel" value="motorista">
    <div class="sigem-card-header">
        <div class="min-w-0 flex-1">
            <h2 class="sigem-card-title">Credenciar motorista</h2>
            <p class="m-0 mt-0.5 text-[13px] text-sigem-muted dark:text-gray-400">O motorista precisa ter usuário ativo no SIGEM. Ele entra como apto a conduzir.</p>
        </div>
    </div>
    <div class="sigem-card-body flex flex-col gap-4">
        @if($usuariosDisponiveis->isEmpty())
            <div class="sigem-alert sigem-alert-info" role="status"><span>Todos os usuários ativos já estão credenciados como motoristas. Cadastre o servidor em Administração › Usuários primeiro.</span></div>
        @endif
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <x-sigem.field label="Servidor" for="user_id" required class="md:col-span-2">
                <select id="user_id" name="user_id" required class="sigem-input">
                    <option value="">Selecione o servidor</option>
                    @foreach($usuariosDisponiveis as $u)
                        <option value="{{ $u->id }}" @selected((string) old('user_id') === (string) $u->id)>{{ $u->name }}{{ $u->cargo ? ' — ' . $u->cargo : '' }}</option>
                    @endforeach
                </select>
            </x-sigem.field>
            <x-sigem.field label="Nº de registro da CNH" for="cnh_numero" required>
                <input id="cnh_numero" name="cnh_numero" type="text" required maxlength="30" inputmode="numeric" value="{{ old('cnh_numero') }}" class="sigem-input">
            </x-sigem.field>
            <x-sigem.field label="Categoria" for="cnh_categoria" required help="Ambulância e ônibus exigem D; carreta, E.">
                <select id="cnh_categoria" name="cnh_categoria" required class="sigem-input">
                    @foreach(['B', 'AB', 'C', 'AC', 'D', 'AD', 'E', 'AE'] as $cat)
                        <option value="{{ $cat }}" @selected(old('cnh_categoria', 'D') === $cat)>{{ $cat }}</option>
                    @endforeach
                </select>
            </x-sigem.field>
            <x-sigem.field label="Validade da CNH" for="cnh_validade" required>
                <input id="cnh_validade" name="cnh_validade" type="date" required value="{{ old('cnh_validade') }}" class="sigem-input">
            </x-sigem.field>
            <x-sigem.field label="Observações" for="observacoes" class="md:col-span-2 lg:col-span-3">
                <input id="observacoes" name="observacoes" type="text" value="{{ old('observacoes') }}" class="sigem-input">
            </x-sigem.field>
        </div>
        <fieldset class="m-0 border-0 p-0">
            <legend class="sigem-label">Cursos especializados (CONTRAN)</legend>
            <div class="flex flex-wrap gap-x-5 gap-y-2">
                @foreach(\App\Models\Motorista::CURSOS as $valor => $rotulo)
                    <label for="curso_{{ $valor }}" class="flex cursor-pointer items-center gap-2 text-sm">
                        <input id="curso_{{ $valor }}" type="checkbox" name="cursos_obrigatorios[]" value="{{ $valor }}" @checked(in_array($valor, (array) old('cursos_obrigatorios', []), true))> {{ $rotulo }}
                    </label>
                @endforeach
            </div>
        </fieldset>
        <div class="flex justify-end gap-2">
            <x-sigem.button @click="painel = ''">Cancelar</x-sigem.button>
            <x-sigem.button type="submit" variant="primary">Credenciar motorista</x-sigem.button>
        </div>
    </div>
</form>
@endif

<div class="sigem-card">
    @if($motoristas->isEmpty())
        <x-sigem.empty title="Nenhum motorista credenciado" description="Credencie os servidores habilitados para que possam ser escalados nas saídas do diário de bordo.">
            @if($podeGerenciar)<x-sigem.button variant="primary" @click="painel = 'motorista'">Credenciar motorista</x-sigem.button>@endif
        </x-sigem.empty>
    @else
        <x-sigem.table min-width="820px">
            <x-slot:head>
                <th scope="col">Motorista</th>
                <th scope="col">CNH</th>
                <th scope="col">Validade</th>
                <th scope="col">Cursos</th>
                <th scope="col">Situação</th>
            </x-slot:head>
            @foreach($motoristas as $m)
                @php $dias = $m->dias_para_vencer_cnh; @endphp
                <tr>
                    <td>
                        <div class="font-semibold">{{ $m->nome }}</div>
                        @if($m->user?->cargo || $m->user?->email)<div class="sigem-muted">{{ $m->user->cargo ?: $m->user->email }}</div>@endif
                    </td>
                    <td class="whitespace-nowrap">
                        Categoria {{ $m->cnh_categoria }}
                        <div class="sigem-muted tabular-nums">{{ $m->cnh_numero }}</div>
                    </td>
                    <td class="whitespace-nowrap">
                        {{ $m->cnh_validade?->format('d/m/Y') ?? '—' }}
                        @if($dias !== null && $dias < 0)
                            <div class="text-xs font-semibold text-sigem-danger dark:text-red-400">Vencida há {{ abs($dias) }} {{ abs($dias) === 1 ? 'dia' : 'dias' }}</div>
                        @elseif($dias !== null && $dias <= 30)
                            <div class="text-xs font-semibold text-sigem-warn dark:text-amber-300">{{ $dias === 0 ? 'Vence hoje' : 'Vence em ' . $dias . ($dias === 1 ? ' dia' : ' dias') }}</div>
                        @endif
                    </td>
                    <td class="max-w-[260px]">
                        @if($m->cursos_formatados)
                            <div class="truncate sigem-muted" title="{{ implode(', ', $m->cursos_formatados) }}">{{ implode(', ', $m->cursos_formatados) }}</div>
                        @else
                            <span class="sigem-muted">—</span>
                        @endif
                    </td>
                    <td class="whitespace-nowrap">
                        <x-sigem.status :tom="$m->status_tom">{{ $m->status_formatado }}</x-sigem.status>
                        @if($m->viagens_em_andamento_count > 0)<div class="sigem-muted mt-1">Em viagem agora</div>@endif
                    </td>
                </tr>
            @endforeach
        </x-sigem.table>
        <div class="flex flex-wrap items-center gap-2 border-t border-sigem-line px-4 py-3 text-[13px] text-sigem-muted dark:border-gray-800 dark:text-gray-400">
            <span class="flex-1">Mostrando {{ $motoristas->firstItem() }}–{{ $motoristas->lastItem() }} de {{ $motoristas->total() }}</span>
            @if($motoristas->hasPages())
                <x-sigem.button size="sm" :href="$motoristas->previousPageUrl()" :class="$motoristas->onFirstPage() ? 'pointer-events-none opacity-50' : ''">Anterior</x-sigem.button>
                <x-sigem.button size="sm" :href="$motoristas->nextPageUrl()" :class="$motoristas->hasMorePages() ? '' : 'pointer-events-none opacity-50'">Próxima</x-sigem.button>
            @endif
        </div>
    @endif
</div>
</div>
@endsection
