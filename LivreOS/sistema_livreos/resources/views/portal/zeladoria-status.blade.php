{{--
 * Componente da aplicação Phoenix - SIGEM
 * Portal do Cidadão — andamento de pedido de serviço
 *
 * @license AGPL-3.0
 --}}
@extends('layouts.portal')

@section('content')
@php use App\Support\StatusZeladoria; @endphp
<div class="mx-auto max-w-3xl">
@if(!$demanda)
    <x-sigem.page-header title="Pedido não encontrado" />
    <div class="sigem-alert sigem-alert-warn mb-5" role="alert"><span>{{ $erro }}</span></div>
    <x-sigem.button href="{{ route('portal.index') }}#consultar" variant="primary">Tentar de novo</x-sigem.button>
@else
    @php
        $s = $demanda->status;
        $etapa = match (true) {
            in_array($s, StatusZeladoria::CONCLUIDAS, true) => 4,
            in_array($s, [StatusZeladoria::EM_EXECUCAO, 'Em andamento', StatusZeladoria::AGUARDANDO_MATERIAL], true) => 3,
            $s === StatusZeladoria::PROGRAMADA => 2,
            default => 1,
        };
        $cancelada = in_array($s, [StatusZeladoria::CANCELADA, StatusZeladoria::IMPROCEDENTE], true);
        $etapas = [1 => 'Recebido', 2 => 'Programado', 3 => 'Em execução', 4 => 'Concluído'];
    @endphp

    <x-sigem.flash />
    <x-sigem.page-header :title="'Pedido ' . $demanda->codigo_interno" :subtitle="($demanda->categoriaZeladoria->nome ?? 'Serviço') . ' — ' . ($completo ? trim(($demanda->logradouro ?? '') . ($demanda->numero_logradouro ? ', ' . $demanda->numero_logradouro : '') . ($demanda->bairro ? ' — ' . $demanda->bairro : '')) : ($demanda->bairro ?: 'local informado'))">
        <x-slot:badges><x-sigem.status :tom="StatusZeladoria::tom($s)">{{ StatusZeladoria::rotulo($s) }}</x-sigem.status></x-slot:badges>
    </x-sigem.page-header>

    <x-sigem.card class="mb-5">
        @if($cancelada)
            <div class="sigem-alert sigem-alert-warn" role="status"><span>Este pedido foi encerrado sem execução ({{ mb_strtolower(StatusZeladoria::rotulo($s)) }}). Em caso de dúvida, procure a ouvidoria da Prefeitura informando o código acima.</span></div>
        @else
            <ol class="m-0 grid list-none grid-cols-4 gap-2 p-0" aria-label="Etapas do pedido">
                @foreach($etapas as $n => $rotulo)
                    <li class="flex flex-col gap-1.5" @if($n === $etapa) aria-current="step" @endif>
                        <span class="h-1.5 rounded {{ $n <= $etapa ? 'bg-sigem-primary' : 'bg-sigem-neutral-soft dark:bg-white/10' }}"></span>
                        <span class="text-[13px] {{ $n === $etapa ? 'font-semibold text-sigem-ink dark:text-white' : 'text-sigem-muted' }}">{{ $rotulo }}</span>
                    </li>
                @endforeach
            </ol>
        @endif
        <dl class="sigem-dl mt-5">
            @if($completo)<div><dt>Solicitante</dt><dd>{{ $dadosRequerente['nome'] }}</dd></div>@endif
            <div><dt>Aberto em</dt><dd>{{ \Carbon\Carbon::parse($demanda->data_abertura ?? $demanda->created_at)->format('d/m/Y') }}</dd></div>
            <div><dt>Responsável</dt><dd>{{ $demanda->secretariaResponsavel->nome ?? 'Prefeitura' }}</dd></div>
            @if($demanda->prevista_conclusao && $etapa < 4 && !$cancelada)
                <div><dt>Previsão</dt><dd>{{ \Carbon\Carbon::parse($demanda->prevista_conclusao)->format('d/m/Y') }}</dd></div>
            @endif
        </dl>
    </x-sigem.card>

    @if($completo)
    <x-sigem.card title="Seu relato" id="t-relato">
        <p class="m-0 whitespace-pre-line">{{ $demanda->relato_cliente }}</p>
        @if($demanda->foto_antes || $demanda->foto_depois)
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                @foreach(['Antes' => $demanda->foto_antes, 'Depois' => $demanda->foto_depois] as $rotulo => $foto)
                    @if($foto)
                        <figure class="m-0">
                            <img src="{{ asset('storage/' . $foto) }}" alt="Foto {{ mb_strtolower($rotulo) }}" class="h-48 w-full rounded-md border border-sigem-line object-cover dark:border-gray-800">
                            <figcaption class="mt-1 text-[13px] text-sigem-muted">{{ $rotulo }}</figcaption>
                        </figure>
                    @endif
                @endforeach
            </div>
        @endif
        @if($etapa === 4 && $demanda->servicos_realizados)
            <div class="sigem-alert sigem-alert-ok mt-4"><span><strong>Serviço executado:</strong> {{ $demanda->servicos_realizados }}</span></div>
        @endif
    </x-sigem.card>

    @else
        <div class="sigem-alert sigem-alert-info" role="status">
            <span>Para ver o relato, o endereço e as fotos, informe a <strong>chave de acesso</strong> do seu comprovante.</span>
        </div>
        <form method="GET" action="{{ route('portal.zeladoria.show', ['codigo' => $demanda->codigo_interno]) }}" class="mt-3 flex flex-wrap items-end gap-2">
            <x-sigem.field label="Chave de acesso" for="chave" class="min-w-[220px]">
                <input id="chave" name="chave" type="text" maxlength="12" class="sigem-input font-mono uppercase" autocomplete="off">
            </x-sigem.field>
            <x-sigem.button type="submit">Ver detalhes</x-sigem.button>
        </form>
    @endif
    @if($completo)
        <p class="mt-5 text-[13px] text-sigem-muted">Guarde o código <strong class="font-mono text-sigem-ink dark:text-white">{{ $demanda->codigo_interno }}</strong> e a chave <strong class="font-mono text-sigem-ink dark:text-white">{{ $demanda->chave_acesso }}</strong> para acompanhar este pedido.</p>
    @endif
@endif
</div>
@endsection
