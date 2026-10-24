{{--
 * Componente da aplicação Phoenix - SIGEM
 * Início do servidor: Minha caixa + indicadores + movimentações (padrão SIGEM Clean)
 *
 * @license AGPL-3.0
 --}}
@extends('layouts.app')

@section('content')
@php
    $nomeCurto = explode(' ', trim(auth()->user()->name))[0];
    $escopo = $gestor ? 'todo o município' : ($lotacao ? ($lotacao->sigla ?: $lotacao->nome) : 'sem lotação');
@endphp

<x-sigem.page-header :title="$saudacao . ', ' . $nomeCurto"
    :subtitle="($lotacao ? $lotacao->nome : 'Sem lotação definida') . ' · ' . now()->translatedFormat('l, d \d\e F \d\e Y')">
    <x-slot:actions>
        @if(Route::has('almoxarifado.requisicoes.create'))
            <x-sigem.button href="{{ route('almoxarifado.requisicoes.create') }}">Nova requisição</x-sigem.button>
        @endif
        <x-sigem.button href="{{ route('zeladoria.create') }}">Nova demanda</x-sigem.button>
        <x-sigem.button variant="primary" href="{{ route('protocolo.create') }}">
            <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Autuar processo
        </x-sigem.button>
    </x-slot:actions>
</x-sigem.page-header>

<x-sigem.flash />

@if(!$lotacao && !$gestor)
    <div class="sigem-alert sigem-alert-warn mb-4" role="status">
        <span>Seu usuário ainda não tem <strong>lotação</strong>. Sem ela você não recebe nem tramita processos. Peça ao administrador para definir sua secretaria em Usuários e permissões.</span>
    </div>
@endif

{{-- MINHA CAIXA --}}
<x-sigem.card :padded="false" class="mb-5" id="t-caixa" title="Minha caixa"
    :subtitle="$caixa->isEmpty() ? 'Nada pendente com você agora.' : $caixa->count() . ' ' . ($caixa->count() === 1 ? 'item aguarda' : 'itens aguardam') . ' ação · ' . $escopo">
    @if($caixa->isEmpty())
        <x-sigem.empty title="Tudo em dia" description="Quando um processo chegar à sua secretaria, ou uma requisição ou demanda precisar de você, ela aparece aqui." />
    @else
        <ul class="m-0 list-none p-0">
            @foreach($caixa as $item)
                <li class="flex flex-wrap items-center gap-x-5 gap-y-2 border-b border-gray-100 px-5 py-3.5 last:border-0 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-white/[0.03]">
                    <span class="w-24 shrink-0 text-xs text-sigem-muted dark:text-gray-400">{{ $item['tipo'] }}</span>
                    <div class="min-w-0 flex-1 basis-72">
                        <a href="{{ $item['url'] }}" class="sigem-link">{{ $item['codigo'] }}</a>
                        <span class="text-sigem-ink dark:text-gray-100"> — {{ $item['titulo'] }}</span>
                        <div class="text-[13px] text-sigem-muted dark:text-gray-400">{{ $item['detalhe'] }}</div>
                    </div>
                    <x-sigem.status :tom="$item['tom']">{{ $item['status'] }}</x-sigem.status>
                    <x-sigem.button :variant="$item['primaria'] ? 'primary' : 'secondary'" size="sm" href="{{ $item['url'] }}">{{ $item['acao'] }}</x-sigem.button>
                </li>
            @endforeach
        </ul>
    @endif
</x-sigem.card>

{{-- INDICADORES --}}
<section aria-label="Indicadores" class="mb-5 grid gap-4" style="grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));">
    <x-sigem.kpi label="Processos em andamento" :value="$indicadores['processos']" href="{{ route('protocolo.index') }}"
        :hint="$indicadores['processos_urgentes'] ? $indicadores['processos_urgentes'] . ' urgente(s)' : 'nenhum urgente'"
        :hint-tone="$indicadores['processos_urgentes'] ? 'danger' : 'muted'" />
    <x-sigem.kpi label="Demandas de zeladoria em triagem" :value="$indicadores['zeladoria_abertas']" href="{{ route('zeladoria.index') }}"
        :hint="$indicadores['zeladoria_campo'] . ' em campo'" />
    <x-sigem.kpi label="Requisições aguardando aprovação" :value="$indicadores['requisicoes_pendentes']" href="{{ route('almoxarifado.requisicoes.index', ['situacao' => 'pendente_aprovacao']) }}"
        :hint="$indicadores['estoque_critico'] ? $indicadores['estoque_critico'] . ' material(is) abaixo do mínimo' : 'estoque acima do mínimo'"
        :hint-tone="$indicadores['estoque_critico'] ? 'warn' : 'muted'" />
    <x-sigem.kpi label="Veículos em viagem" :value="$indicadores['veiculos_viagem']" href="{{ route('frota.index') }}"
        :hint="$indicadores['cnh_vencendo'] ? $indicadores['cnh_vencendo'] . ' CNH vence(m) em 30 dias' : 'CNHs em dia'"
        :hint-tone="$indicadores['cnh_vencendo'] ? 'warn' : 'muted'" />
</section>

<div class="flex flex-wrap gap-5">
    {{-- Movimentações --}}
    <x-sigem.card :padded="false" class="min-w-0 flex-[3_1_420px]" title="Últimas movimentações" id="t-mov">
        <x-slot:actions><a href="{{ route('protocolo.index') }}" class="sigem-link text-[13px] font-normal">Ver processos</a></x-slot:actions>
        @if($movimentacoes->isEmpty())
            <x-sigem.empty title="Nenhuma movimentação ainda" />
        @else
            <ol class="m-0 list-none px-5 py-2">
                @foreach($movimentacoes as $mov)
                    <li class="flex gap-3 border-b border-gray-100 py-2.5 last:border-0 dark:border-gray-800">
                        <span class="w-20 shrink-0 text-[13px] text-sigem-muted dark:text-gray-400" title="{{ optional($mov->data_envio)->format('d/m/Y H:i') }}">{{ optional($mov->data_envio)->isToday() ? $mov->data_envio->format('H:i') : optional($mov->data_envio)->format('d/m') }}</span>
                        <div class="min-w-0">
                            @if($mov->processo)<a href="{{ route('protocolo.show', $mov->processo) }}" class="sigem-link">{{ $mov->processo->numero }}</a>@endif
                            @if($mov->status === 'concluido')
                                decidido em <strong>{{ $mov->unidadeOrigem->sigla ?? '—' }}</strong>
                            @elseif($mov->unidade_origem_id === $mov->unidade_destino_id)
                                autuado em <strong>{{ $mov->unidadeDestino->sigla ?? '—' }}</strong>
                            @else
                                enviado de <strong>{{ $mov->unidadeOrigem->sigla ?? '—' }}</strong> para <strong>{{ $mov->unidadeDestino->sigla ?? '—' }}</strong>
                            @endif
                            <div class="truncate text-[13px] text-sigem-muted dark:text-gray-400">{{ \Illuminate\Support\Str::limit($mov->despacho, 110) }}</div>
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif
    </x-sigem.card>

    {{-- Processos por secretaria --}}
    <x-sigem.card class="min-w-0 flex-[2_1_300px]" title="Processos por secretaria" id="t-carga">
        <div class="flex flex-col gap-3">
            @forelse($porSecretaria as $linha)
                <div class="grid items-center gap-2.5" style="grid-template-columns: 72px minmax(0,1fr) 28px;" title="{{ $linha['nome'] }}">
                    <span class="truncate text-[13px]">{{ $linha['sigla'] }}</span>
                    <span class="h-2 rounded bg-sigem-neutral-soft dark:bg-white/10"><span class="block h-2 rounded bg-sigem-primary" style="width: {{ round($linha['total'] / $maiorCarga * 100) }}%"></span></span>
                    <span class="text-right text-[13px]">{{ $linha['total'] }}</span>
                </div>
            @empty
                <p class="sigem-muted m-0">Nenhuma secretaria cadastrada.</p>
            @endforelse
        </div>
    </x-sigem.card>
</div>

{{-- Mapa de ocorrências abertas --}}
<x-sigem.card :padded="false" class="mt-5" title="Ocorrências de zeladoria em aberto" id="t-mapa"
    :subtitle="$ocorrenciasMapa->count() . ' no mapa'">
    <x-slot:actions><a href="{{ route('zeladoria.index') }}" class="sigem-link text-[13px] font-normal">Abrir zeladoria</a></x-slot:actions>
    <div id="mapaInicio" class="h-[300px] w-full rounded-b-lg" role="region" aria-label="Mapa das ocorrências abertas"></div>
</x-sigem.card>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.sigemMapa) return;
    var pontos = @json($ocorrenciasMapa);
    var mapa = window.sigemMapa('mapaInicio');
    if (!mapa) return;
    var limites = [];
    pontos.forEach(function (p) {
        if (!p.lat || !p.lng) return;
        limites.push([p.lat, p.lng]);
        L.circleMarker([p.lat, p.lng], { radius: 7, color: '#ffffff', weight: 2, fillColor: '#1351b4', fillOpacity: 0.9 })
            .addTo(mapa)
            .bindPopup('<strong>' + sigemEsc(p.codigo) + '</strong><br>' + sigemEsc(p.categoria) + '<br><small>' + sigemEsc(p.local) + ' · ' + sigemEsc(p.situacao) + '</small><br><a href="' + sigemEsc(p.url) + '">Abrir demanda</a>');
    });
    if (limites.length) mapa.fitBounds(limites, { padding: [30, 30], maxZoom: 15 });
});
</script>
@endpush
