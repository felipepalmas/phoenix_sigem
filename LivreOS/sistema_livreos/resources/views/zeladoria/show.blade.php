{{--
 * Componente da aplicação Phoenix - SIGEM
 * Zeladoria — detalhe da demanda: andamento, fotos antes/depois e atesto (padrão SIGEM Clean)
 *
 * @license AGPL-3.0
 --}}
@extends('layouts.app')

@section('content')
@php
    use App\Support\StatusZeladoria;
    $codigo = $os->codigo_interno ?: '#' . $os->id;
    $abertura = $os->data_abertura ? \Carbon\Carbon::parse($os->data_abertura) : $os->created_at;
    $previsao = $os->prevista_conclusao ? \Carbon\Carbon::parse($os->prevista_conclusao) : null;
    $painelInicial = old('_painel', '');
    $prio = mb_strtolower((string) $os->prioridade);
    $origens = ['portal' => 'Portal do Cidadão', 'telefone_156' => 'Telefone 156', 'balcao' => 'Balcão', 'ouvidoria' => 'Ouvidoria', 'fiscalizacao' => 'Fiscalização', 'processo_protocolo' => 'Processo'];
    $proximas = [
        StatusZeladoria::PROGRAMADA => 'Programar',
        StatusZeladoria::EM_EXECUCAO => 'Iniciar execução',
        StatusZeladoria::AGUARDANDO_MATERIAL => 'Aguardando material',
    ];
@endphp

<div x-data="{ painel: @js($painelInicial) }">
<x-sigem.page-header :title="'Demanda ' . $codigo" :subtitle="($os->categoriaZeladoria->nome ?? 'Zeladoria') . ' — ' . ($os->endereco_completo ?: 'local não informado')"
    :breadcrumbs="['Zeladoria' => route('zeladoria.index'), $codigo => null]">
    <x-slot:badges>
        <x-sigem.status :tom="StatusZeladoria::tom($os->status)">{{ StatusZeladoria::rotulo($os->status) }}</x-sigem.status>
        @if(in_array($prio, ['urgente', 'emergencial', 'alta'], true))<x-sigem.status tom="danger">{{ $prio === 'emergencial' ? 'Emergencial' : 'Urgente' }}</x-sigem.status>@endif
    </x-slot:badges>
    <x-slot:actions>
        @if(!$encerrada && $podeOperar)
            <x-sigem.button variant="primary" @click="painel = painel === 'concluir' ? '' : 'concluir'">Concluir com foto</x-sigem.button>
            <x-sigem.button @click="painel = painel === 'andamento' ? '' : 'andamento'">Atualizar situação</x-sigem.button>
        @endif
    </x-slot:actions>
</x-sigem.page-header>

<x-sigem.flash />

@if(!$encerrada && !$podeOperar)
    <div class="sigem-alert sigem-alert-info mb-4" role="status">
        <span>Esta demanda é da <strong>{{ $os->secretariaResponsavel->nome ?? 'secretaria responsável' }}</strong>. Só servidores lotados nela (com permissão de zeladoria) podem atualizá-la.</span>
    </div>
@endif

@if(!$encerrada && $podeOperar)
<form x-show="painel === 'andamento'" x-cloak method="POST" action="{{ route('zeladoria.andamento', $os) }}" class="sigem-card mb-5 border-sigem-primary">
    @csrf
    <input type="hidden" name="_painel" value="andamento">
    <div class="sigem-card-header"><h2 class="sigem-card-title">Atualizar situação</h2></div>
    <div class="sigem-card-body flex flex-col gap-4">
        <fieldset class="m-0 border-0 p-0">
            <legend class="sigem-label">Nova situação</legend>
            <div class="flex flex-wrap gap-2">
                @foreach($proximas + [StatusZeladoria::CANCELADA => 'Cancelar', StatusZeladoria::IMPROCEDENTE => 'Improcedente'] as $valor => $rotulo)
                    <label class="flex cursor-pointer items-center gap-2 rounded-md border border-sigem-field px-3 py-2 text-sm has-[:checked]:border-sigem-primary has-[:checked]:bg-sigem-primary-soft dark:border-gray-700">
                        <input type="radio" name="status" value="{{ $valor }}" required @checked(old('status') === $valor)> {{ $rotulo }}
                    </label>
                @endforeach
            </div>
        </fieldset>
        <x-sigem.field label="Observação" for="observacao" help="Ex.: equipe e data programadas, material em falta, motivo do cancelamento.">
            <textarea id="observacao" name="observacao" rows="2" maxlength="1000" class="sigem-input">{{ old('observacao') }}</textarea>
        </x-sigem.field>
        <div class="flex justify-end gap-2">
            <x-sigem.button @click="painel = ''">Cancelar</x-sigem.button>
            <x-sigem.button type="submit" variant="primary">Salvar situação</x-sigem.button>
        </div>
    </div>
</form>

<form x-show="painel === 'concluir'" x-cloak method="POST" action="{{ route('zeladoria.concluir', $os) }}" enctype="multipart/form-data" class="sigem-card mb-5 border-sigem-primary">
    @csrf
    <input type="hidden" name="_painel" value="concluir">
    <div class="sigem-card-header"><h2 class="sigem-card-title">Concluir demanda</h2></div>
    <div class="sigem-card-body flex flex-col gap-4">
        <x-sigem.field label="Foto do serviço pronto (depois)" for="foto_depois" required help="Obrigatória: comprova a execução para o cidadão e para a auditoria.">
            <input id="foto_depois" name="foto_depois" type="file" required accept="image/*" class="sigem-input h-auto py-1.5">
        </x-sigem.field>
        <x-sigem.field label="Serviço executado" for="servicos_realizados" required>
            <textarea id="servicos_realizados" name="servicos_realizados" rows="3" required minlength="5" class="sigem-input" placeholder="Ex.: troca de lâmpada por LED 100 W no poste 12">{{ old('servicos_realizados') }}</textarea>
        </x-sigem.field>
        <x-sigem.field label="Seu cargo no atesto" for="atesto_cargo" help="O atesto é registrado em nome de {{ auth()->user()->name }}, com data e hora.">
            <input id="atesto_cargo" name="atesto_cargo" type="text" maxlength="100" value="{{ old('atesto_cargo', auth()->user()->cargo) }}" class="sigem-input md:max-w-sm">
        </x-sigem.field>
        <div class="flex justify-end gap-2">
            <x-sigem.button @click="painel = ''">Cancelar</x-sigem.button>
            <x-sigem.button type="submit" variant="primary">Concluir e atestar</x-sigem.button>
        </div>
    </div>
</form>
@endif

<div class="flex flex-wrap items-start gap-5">
    <div class="flex min-w-0 flex-[2_1_480px] flex-col gap-5">
        <x-sigem.card title="Relato" id="t-relato">
            <p class="m-0 whitespace-pre-line">{{ $os->relato_cliente ?: 'Sem relato.' }}</p>
            <dl class="sigem-dl mt-4 border-t border-gray-100 pt-4 dark:border-gray-800">
                <div><dt>Aberta em</dt><dd>{{ $abertura?->format('d/m/Y H:i') }}</dd></div>
                <div><dt>Origem</dt><dd>{{ $origens[$os->origem_demanda] ?? 'Interna' }}</dd></div>
                <div><dt>Previsão</dt><dd class="{{ $previsao && !$encerrada && $previsao->isPast() ? 'text-sigem-danger' : '' }}">{{ $previsao ? $previsao->format('d/m/Y') : '—' }}</dd></div>
                <div><dt>Secretaria</dt><dd>{{ $os->secretariaResponsavel->sigla ?? ($os->secretariaResponsavel->nome ?? '—') }}</dd></div>
            </dl>
        </x-sigem.card>

        <x-sigem.card title="Fotos" id="t-fotos">
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach(['Antes' => $os->foto_antes, 'Depois' => $os->foto_depois] as $rotulo => $foto)
                    <figure class="m-0">
                        @if($foto)
                            <a href="{{ asset('storage/' . $foto) }}" target="_blank" rel="noopener">
                                <img src="{{ asset('storage/' . $foto) }}" alt="Foto {{ mb_strtolower($rotulo) }} do local" class="h-56 w-full rounded-md border border-sigem-line object-cover dark:border-gray-800">
                            </a>
                        @else
                            <div class="flex h-56 items-center justify-center rounded-md border border-dashed border-sigem-field text-[13px] text-sigem-muted dark:border-gray-700">Sem foto</div>
                        @endif
                        <figcaption class="mt-1 text-[13px] text-sigem-muted">{{ $rotulo }}</figcaption>
                    </figure>
                @endforeach
            </div>
            @if($os->atesto_responsavel)
                <div class="sigem-alert sigem-alert-ok mt-4">
                    <div>
                        <strong>Execução atestada</strong> por {{ $os->atesto_responsavel }}{{ $os->atesto_cargo ? ' (' . $os->atesto_cargo . ')' : '' }}{{ $os->atesto_em ? ' em ' . \Carbon\Carbon::parse($os->atesto_em)->format('d/m/Y H:i') : '' }}.
                        @if($os->servicos_realizados)<div class="mt-1">{{ $os->servicos_realizados }}</div>@endif
                    </div>
                </div>
            @endif
        </x-sigem.card>

        @if($os->observacoes_internas)
            <x-sigem.card title="Andamento" id="t-and">
                <ul class="m-0 flex list-none flex-col gap-2 p-0">
                    @foreach(array_reverse(array_filter(explode("\n", $os->observacoes_internas))) as $linha)
                        <li class="border-b border-gray-100 pb-2 text-[13px] last:border-0 dark:border-gray-800">{{ $linha }}</li>
                    @endforeach
                </ul>
            </x-sigem.card>
        @endif
    </div>

    <aside class="flex min-w-0 flex-[1_1_280px] flex-col gap-5">
        <x-sigem.card :padded="false" title="Local" id="t-local">
            <div class="px-5 py-3">
                <div class="font-semibold">{{ $os->endereco_completo ?: ($os->logradouro ?? '—') }}</div>
                @if($os->referencia_local)<div class="sigem-muted">Referência: {{ $os->referencia_local }}</div>@endif
            </div>
            @if($os->latitude && $os->longitude)
                <div id="mapaDemanda" class="h-[220px] w-full rounded-b-lg" role="region" aria-label="Mapa do local"></div>
            @else
                <p class="sigem-muted m-0 px-5 pb-4">Sem ponto no mapa.</p>
            @endif
        </x-sigem.card>

        <x-sigem.card title="Solicitante" id="t-sol">
            <div class="font-semibold">{{ $os->cliente?->nome ?: ($os->cliente?->razao_social ?? '—') }}</div>
            @if($os->cliente && Route::has('clientes.show'))
                <a href="{{ route('clientes.show', $os->cliente) }}" class="sigem-link mt-1 inline-block text-[13px] font-normal">Abrir cadastro</a>
            @endif
        </x-sigem.card>

        @if($os->chave_acesso)
            <x-sigem.card title="Consulta pública" id="t-consulta">
                <p class="m-0 text-[13px] text-sigem-muted dark:text-gray-400">Informe ao cidadão para acompanhar no portal:</p>
                <dl class="sigem-dl mt-2">
                    <div><dt>Código</dt><dd class="font-mono">{{ $os->codigo_interno }}</dd></div>
                    <div><dt>Chave</dt><dd class="font-mono tracking-wider">{{ $os->chave_acesso }}</dd></div>
                </dl>
            </x-sigem.card>
        @endif

        @if($os->processo)
            <x-sigem.card title="Processo vinculado" id="t-proc">
                <a href="{{ route('protocolo.show', $os->processo) }}" class="sigem-link">{{ $os->processo->numero }}</a>
                <div class="sigem-muted">{{ $os->processo->assuntoPara(auth()->user()) }}</div>
            </x-sigem.card>
        @endif
    </aside>
</div>
</div>
@endsection

@if($os->latitude && $os->longitude)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.sigemMapa) return;
    var ponto = [{{ (float) $os->latitude }}, {{ (float) $os->longitude }}];
    var mapa = window.sigemMapa('mapaDemanda', { centro: ponto, zoom: 16 });
    if (mapa) L.marker(ponto).addTo(mapa);
});
</script>
@endpush
@endif
