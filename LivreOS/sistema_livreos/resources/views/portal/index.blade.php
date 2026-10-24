{{--
 * Componente da aplicação Phoenix - SIGEM
 * Portal do Cidadão — página inicial (padrão SIGEM Clean, linguagem simples)
 *
 * @license AGPL-3.0
 --}}
@extends('layouts.portal')

@section('content')
<x-sigem.flash />

<section class="rounded-lg bg-sigem-primary px-6 py-9 text-white md:px-10">
    <h1 class="m-0 text-[26px] font-semibold leading-tight md:text-[30px]">Como podemos ajudar?</h1>
    <p class="m-0 mt-2 max-w-2xl text-[15px] text-blue-100">Peça um serviço para a sua rua, acompanhe um pedido ou confira se um documento da Prefeitura é verdadeiro.</p>
    <div class="mt-6 grid gap-3 md:grid-cols-3">
        <a href="{{ route('portal.zeladoria.nova') }}" class="flex flex-col gap-1 rounded-lg bg-white p-4 text-sigem-ink no-underline hover:bg-blue-50">
            <strong class="text-[15px]">Pedir um serviço</strong>
            <span class="text-[13px] text-sigem-muted">Lâmpada queimada, buraco, poda, limpeza, bueiro entupido</span>
        </a>
        <a href="#consultar" class="flex flex-col gap-1 rounded-lg bg-white p-4 text-sigem-ink no-underline hover:bg-blue-50">
            <strong class="text-[15px]">Acompanhar um pedido</strong>
            <span class="text-[13px] text-sigem-muted">Processo (com chave de acesso) ou demanda de serviço</span>
        </a>
        <a href="{{ route('portal.autenticidade') }}" class="flex flex-col gap-1 rounded-lg bg-white p-4 text-sigem-ink no-underline hover:bg-blue-50">
            <strong class="text-[15px]">Validar um documento</strong>
            <span class="text-[13px] text-sigem-muted">Confira o código impresso no documento</span>
        </a>
    </div>
</section>

<section id="consultar" class="mt-8 grid gap-5 md:grid-cols-2" aria-label="Acompanhar pedidos">
    <x-sigem.card title="Acompanhar um processo" id="t-proc" subtitle="Use o número e a chave de acesso do seu comprovante de protocolo.">
        <form method="GET" action="{{ route('portal.processos') }}" class="flex flex-col gap-3">
            <x-sigem.field label="Número do processo" for="numero" required>
                <input id="numero" name="numero" type="text" required placeholder="2026/000123" value="{{ request('numero') }}" class="sigem-input font-mono" inputmode="numeric" autocomplete="off">
            </x-sigem.field>
            <x-sigem.field label="Chave de acesso" for="chave" required help="8 letras e números, impressa no comprovante.">
                <input id="chave" name="chave" type="text" required maxlength="12" placeholder="Ex.: K7M4Q2XP" class="sigem-input font-mono uppercase" autocomplete="off">
            </x-sigem.field>
            <div><x-sigem.button type="submit" variant="primary">Consultar processo</x-sigem.button></div>
        </form>
    </x-sigem.card>

    <x-sigem.card title="Acompanhar um pedido de serviço" id="t-zel" subtitle="Use o código recebido ao fazer o pedido (começa com ZEL).">
        <form method="GET" action="{{ route('portal.processos') }}" class="flex flex-col gap-3">
            <x-sigem.field label="Código do pedido" for="codigo-zel" required>
                <input id="codigo-zel" name="numero" type="text" required placeholder="ZEL-2026/00045" class="sigem-input font-mono uppercase" autocomplete="off">
            </x-sigem.field>
            <x-sigem.field label="Chave de acesso" for="chave-zel" help="Opcional. Com a chave você vê também o relato, o endereço e as fotos.">
                <input id="chave-zel" name="chave" type="text" maxlength="12" class="sigem-input font-mono uppercase" autocomplete="off">
            </x-sigem.field>
            <div><x-sigem.button type="submit">Ver andamento</x-sigem.button></div>
        </form>
    </x-sigem.card>
</section>

<section class="mt-8" aria-labelledby="t-numeros">
    <h2 id="t-numeros" class="m-0 mb-3 text-base font-semibold">A Prefeitura em números</h2>
    <div class="grid gap-4" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
        <x-sigem.kpi label="Processos digitais" :value="$stats['processos_total']" :hint="$stats['processos_concluidos'] . ' com decisão'" />
        <x-sigem.kpi label="Pedidos de serviço atendidos" :value="$stats['zeladoria_atendida']" :hint="$stats['zeladoria_ativa'] . ' em andamento'" />
        <x-sigem.kpi label="Veículos da frota pública" :value="$stats['veiculos_operacao']" />
    </div>
</section>

@if($categoriasZeladoria->isNotEmpty())
<section class="mt-8" aria-labelledby="t-servicos">
    <h2 id="t-servicos" class="m-0 mb-3 text-base font-semibold">Serviços que você pode pedir</h2>
    <ul class="m-0 grid list-none gap-3 p-0" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));">
        @foreach($categoriasZeladoria as $cat)
            <li>
                <a href="{{ route('portal.zeladoria.nova', ['categoria' => $cat->id]) }}" class="sigem-card flex flex-col gap-1 p-4 text-sigem-ink no-underline hover:border-sigem-field hover:bg-gray-50 dark:text-white dark:hover:bg-white/5">
                    <strong>{{ $cat->nome }}</strong>
                    @if($cat->prazo_padrao_dias)<span class="text-[13px] text-sigem-muted">Prazo de referência: {{ $cat->prazo_padrao_dias }} dias</span>@endif
                </a>
            </li>
        @endforeach
    </ul>
</section>
@endif

@if($secretarias->isNotEmpty())
<section class="mt-8" aria-labelledby="t-sec">
    <h2 id="t-sec" class="m-0 mb-3 text-base font-semibold">Secretarias</h2>
    <x-sigem.card :padded="false">
        <ul class="m-0 list-none p-0">
            @foreach($secretarias as $sec)
                <li class="flex flex-wrap gap-x-4 gap-y-1 border-b border-gray-100 px-5 py-3 last:border-0 dark:border-gray-800">
                    <strong class="min-w-[90px]">{{ $sec->sigla }}</strong>
                    <span class="min-w-0 flex-1">{{ $sec->nome }}</span>
                    @if($sec->telefone)<span class="text-[13px] text-sigem-muted">{{ $sec->telefone }}</span>@endif
                    @if($sec->email)<a href="mailto:{{ $sec->email }}" class="sigem-link text-[13px] font-normal">{{ $sec->email }}</a>@endif
                </li>
            @endforeach
        </ul>
    </x-sigem.card>
</section>
@endif
@endsection
