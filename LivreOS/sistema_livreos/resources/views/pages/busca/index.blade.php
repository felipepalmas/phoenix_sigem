{{--
 * Componente da aplicação Phoenix - SIGEM
 * Busca global — resultados agrupados por módulo, respeitando permissões (padrão SIGEM Clean)
 *
 * @license AGPL-3.0
 --}}
@extends('layouts.app')

@section('content')
@php
    use App\Support\StatusZeladoria;
    $tomReq = ['pendente_aprovacao' => 'info', 'aprovada' => 'info', 'atendida_parcial' => 'warn', 'atendida' => 'ok', 'concluida' => 'ok', 'recusada' => 'danger', 'cancelada' => 'neutral'];
    $secoes = [
        'processos' => ['Processos', route('protocolo.index')],
        'zeladoria' => ['Demandas de zeladoria', route('zeladoria.index')],
        'requisicoes' => ['Requisições de material', route('almoxarifado.requisicoes.index')],
        'materiais' => ['Materiais', Route::has('produtos.index') ? route('produtos.index') : null],
        'cidadaos' => ['Cidadãos', route('clientes.index')],
        'secretarias' => ['Secretarias', route('secretarias.index')],
        'veiculos' => ['Veículos', route('frota.index')],
    ];
@endphp

<x-sigem.page-header title="Buscar no sistema"
    :subtitle="$termo !== '' ? $totalEncontrado . ' ' . ($totalEncontrado === 1 ? 'resultado' : 'resultados') . ' para “' . $termo . '”' : 'Procure por número de processo, código de demanda, placa, CPF/CNPJ, nome ou endereço.'" />

<form action="{{ route('busca.global') }}" method="GET" role="search" class="mb-5 flex max-w-2xl gap-2">
    <label for="busca-pagina" class="sr-only">Termo de busca</label>
    <input id="busca-pagina" type="search" name="q" value="{{ $termo }}" autofocus placeholder="Ex.: 2026/000012, ZEL-2026/00115, ABC1D23, Maria da Silva" class="sigem-input flex-1">
    <x-sigem.button type="submit" variant="primary">Buscar</x-sigem.button>
</form>

@if($termo === '')
    <x-sigem.card>
        <x-sigem.empty title="Digite o que procura" description="A busca mostra só o que o seu perfil pode ver: processos sigilosos e dados de outras secretarias ficam de fora conforme sua lotação e permissões." />
    </x-sigem.card>
@elseif($totalEncontrado === 0)
    <x-sigem.card>
        <x-sigem.empty title="Nada encontrado" description="Confira a digitação ou tente só uma parte do termo (ex.: apenas o número, sem o ano).">
            <x-sigem.button href="{{ route('protocolo.create') }}">Autuar processo</x-sigem.button>
        </x-sigem.empty>
    </x-sigem.card>
@else
    <div class="flex flex-col gap-5">
        @foreach($secoes as $chave => [$titulo, $link])
            @php $itens = $resultados[$chave] ?? collect(); @endphp
            @continue($itens->isEmpty())
            <x-sigem.card :padded="false" :title="$titulo" :subtitle="$itens->count() . ($itens->count() === 1 ? ' resultado' : ' resultados')" :id="'b-' . $chave">
                <x-slot:actions>
                    @if($link)
                        <a href="{{ $link }}" class="sigem-link text-[13px] font-normal">Abrir módulo</a>
                    @endif
                </x-slot:actions>
                <ul class="m-0 list-none p-0">
                    @foreach($itens as $item)
                        <li class="flex flex-wrap items-center gap-x-4 gap-y-1 border-b border-gray-100 px-5 py-3 last:border-0 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-white/[0.03]">
                            @switch($chave)
                                @case('processos')
                                    <div class="min-w-0 flex-1">
                                        <a href="{{ route('protocolo.show', $item) }}" class="sigem-link">{{ $item->numero }}</a> — {{ $item->assunto }}
                                        <div class="sigem-muted truncate">{{ $item->interessado?->nome ?: $item->interessado?->razao_social }} · {{ $item->unidadeAtual?->sigla ?: $item->unidadeAtual?->nome }}</div>
                                    </div>
                                    <x-sigem.status :tom="$item->status_tom">{{ $item->status_rotulo }}</x-sigem.status>
                                    @break
                                @case('zeladoria')
                                    <div class="min-w-0 flex-1">
                                        <a href="{{ route('zeladoria.show', $item) }}" class="sigem-link">{{ $item->codigo_interno ?: '#' . $item->id }}</a> — {{ $item->categoriaZeladoria->nome ?? 'Zeladoria' }}
                                        <div class="sigem-muted truncate">{{ trim(($item->logradouro ?? '') . ($item->bairro ? ', ' . $item->bairro : '')) }}</div>
                                    </div>
                                    <x-sigem.status :tom="StatusZeladoria::tom($item->status)">{{ StatusZeladoria::rotulo($item->status) }}</x-sigem.status>
                                    @break
                                @case('requisicoes')
                                    <div class="min-w-0 flex-1">
                                        <a href="{{ route('almoxarifado.requisicoes.show', $item) }}" class="sigem-link">{{ $item->numero }}</a> — {{ $item->unidadeSolicitante?->sigla ?: $item->unidadeSolicitante?->nome }}
                                        <div class="sigem-muted truncate">{{ \Illuminate\Support\Str::limit($item->justificativa, 120) }}</div>
                                    </div>
                                    <x-sigem.status :tom="$tomReq[$item->situacao] ?? 'neutral'">{{ $item->situacao_formatada }}</x-sigem.status>
                                    @break
                                @case('materiais')
                                    <div class="min-w-0 flex-1">
                                        @if(Route::has('produtos.show'))<a href="{{ route('produtos.show', $item) }}" class="sigem-link">{{ $item->nome }}</a>@else<strong>{{ $item->nome }}</strong>@endif
                                        <div class="sigem-muted">{{ $item->codigo_sku ? 'Código ' . $item->codigo_sku . ' · ' : '' }}Saldo {{ rtrim(rtrim(number_format((float) $item->estoque_quantidade, 2, ',', '.'), '0'), ',') }} {{ $item->unidade }}</div>
                                    </div>
                                    @if((float) $item->estoque_minimo > 0 && (float) $item->estoque_quantidade <= (float) $item->estoque_minimo)
                                        <x-sigem.status tom="warn">Abaixo do mínimo</x-sigem.status>
                                    @endif
                                    @break
                                @case('cidadaos')
                                    <div class="min-w-0 flex-1">
                                        <a href="{{ route('clientes.show', $item) }}" class="sigem-link">{{ $item->nome ?: $item->razao_social }}</a>
                                        <div class="sigem-muted">{{ $item->cpf ? 'CPF ' . $item->cpf : ($item->cnpj ? 'CNPJ ' . $item->cnpj : 'Sem documento') }}</div>
                                    </div>
                                    @break
                                @case('secretarias')
                                    <div class="min-w-0 flex-1">
                                        <a href="{{ auth()->user()->is_admin ? route('secretarias.edit', $item) : route('secretarias.index', ['busca' => $item->sigla ?: $item->nome]) }}" class="sigem-link">{{ $item->sigla ? $item->sigla . ' — ' : '' }}{{ $item->nome }}</a>
                                        @if($item->responsavel)<div class="sigem-muted">Responsável: {{ $item->responsavel->name }}</div>@endif
                                    </div>
                                    @break
                                @case('veiculos')
                                    <div class="min-w-0 flex-1">
                                        <a href="{{ route('frota.show', $item) }}" class="sigem-link font-mono">{{ $item->placa }}</a> — {{ $item->marca }} {{ $item->modelo }}
                                        <div class="sigem-muted">{{ $item->tipo_formatado }} · {{ $item->unidadeAdministrativa?->sigla ?: 'sem secretaria' }}</div>
                                    </div>
                                    <x-sigem.status :tom="$item->status_tom">{{ $item->status_formatado }}</x-sigem.status>
                                    @break
                            @endswitch
                        </li>
                    @endforeach
                </ul>
            </x-sigem.card>
        @endforeach
    </div>
@endif
@endsection
