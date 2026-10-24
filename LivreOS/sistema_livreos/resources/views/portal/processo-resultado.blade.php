{{--
 * Componente da aplicação Phoenix - SIGEM
 * Portal do Cidadão — andamento de processo (número + chave de acesso)
 *
 * @license AGPL-3.0
 --}}
@extends('layouts.portal')

@section('content')
<div class="mx-auto max-w-3xl">
@if(!$processo)
    <x-sigem.page-header title="Processo não encontrado" />
    <div class="sigem-alert sigem-alert-warn mb-5" role="alert"><span>{{ $erro }}</span></div>
    <x-sigem.button href="{{ route('portal.index') }}#consultar" variant="primary">Tentar de novo</x-sigem.button>
@elseif($isSigiloso)
    <x-sigem.page-header :title="'Processo ' . $processo->numero" />
    <div class="sigem-alert sigem-alert-info" role="status">
        <span>Este processo tem acesso restrito por lei (LAI, Lei 12.527/2011, art. 23, ou dados pessoais protegidos pela LGPD). Para ter vista dos autos, procure o Protocolo da Prefeitura com documento de identificação com foto.</span>
    </div>
@else
    @php
        $rotuloParecer = ['favoravel' => 'Favorável', 'desfavoravel' => 'Desfavorável', 'informativo' => 'Encaminhamento', 'diligencia' => 'Pedido de complemento'];
    @endphp
    <x-sigem.page-header :title="'Processo ' . $processo->numero" :subtitle="$processo->assunto">
        <x-slot:badges><x-sigem.status :tom="$processo->status_tom">{{ $processo->status_rotulo }}</x-sigem.status></x-slot:badges>
    </x-sigem.page-header>

    <x-sigem.card class="mb-5">
        <dl class="sigem-dl">
            <div><dt>Requerente</dt><dd>{{ $dadosRequerente['nome'] }}</dd></div>
            <div><dt>CPF</dt><dd class="font-mono">{{ $dadosRequerente['cpf'] }}</dd></div>
            <div><dt>Aberto em</dt><dd>{{ $processo->created_at->format('d/m/Y') }}</dd></div>
            <div><dt>Está em</dt><dd>{{ $processo->unidadeAtual?->nome ?? '—' }}</dd></div>
            @if($processo->data_limite && !$processo->estaEncerrado())
                <div><dt>Prazo de resposta</dt><dd>{{ $processo->data_limite->format('d/m/Y') }}</dd></div>
            @endif
            @if($processo->data_conclusao)
                <div><dt>Decisão em</dt><dd>{{ $processo->data_conclusao->format('d/m/Y') }}</dd></div>
            @endif
        </dl>
    </x-sigem.card>

    <x-sigem.card title="Andamento" id="t-andamento">
        @if($processo->tramitacoes->isEmpty())
            <p class="sigem-muted m-0">Sem movimentações registradas.</p>
        @else
            <x-sigem.timeline>
                @foreach($processo->tramitacoes as $t)
                    <x-sigem.timeline-item :destaque="$loop->first" :ultimo="$loop->last">
                        <div class="flex flex-wrap items-baseline gap-x-3">
                            <strong>
                                @if($t->status === 'concluido') Decisão — {{ $t->unidadeOrigem?->nome }}
                                @elseif($t->unidade_origem_id === $t->unidade_destino_id) Pedido registrado — {{ $t->unidadeDestino?->nome }}
                                @else Enviado para {{ $t->unidadeDestino?->nome }}
                                @endif
                            </strong>
                            <span class="text-[13px] text-sigem-muted">{{ optional($t->data_envio)->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="text-[13px] text-sigem-muted">{{ $rotuloParecer[$t->parecer_tipo] ?? '' }}</div>
                        @if($t->despacho)<p class="m-0 mt-1 whitespace-pre-line">{{ $t->despacho }}</p>@endif
                    </x-sigem.timeline-item>
                @endforeach
            </x-sigem.timeline>
        @endif
    </x-sigem.card>

    @if($processo->documentos->isNotEmpty())
        <x-sigem.card class="mt-5" title="Documentos do processo" id="t-docs" subtitle="Para conferir a autenticidade de um documento impresso, use o código SHA-256 em Validar documento.">
            <ul class="m-0 flex list-none flex-col gap-2 p-0">
                @foreach($processo->documentos as $doc)
                    <li class="flex flex-wrap justify-between gap-2 border-b border-gray-100 pb-2 last:border-0 dark:border-gray-800">
                        <span>{{ $doc->titulo }}</span>
                        <span class="text-[13px] text-sigem-muted">{{ $doc->created_at->format('d/m/Y') }}</span>
                    </li>
                @endforeach
            </ul>
        </x-sigem.card>
    @endif
@endif
</div>
@endsection
