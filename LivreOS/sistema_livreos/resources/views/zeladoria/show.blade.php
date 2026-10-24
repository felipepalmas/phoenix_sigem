{{--
 * Componente da aplicação Phoenix - SIGEM
 * Módulo B: Zeladoria Urbana e Serviços Públicos Municipais
 * Detalhamento da Ocorrência, Mapa, Comparativo Antes/Depois e Atesto
 *
 * @license AGPL-3.0
 --}}

@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
            <a href="{{ route('zeladoria.index') }}" class="hover:underline">Zeladoria Municipal</a>
            <span>/</span>
            <span class="font-semibold text-gray-700 dark:text-gray-300">Demanda {{ $os->codigo_interno ?: '#' . $os->id }}</span>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                {{ $os->codigo_interno ?: 'OS #' . $os->id }}
            </h1>
            @if($os->categoriaZeladoria)
            <span class="inline-flex rounded-md px-3 py-1 text-xs font-bold {{ $os->categoriaZeladoria->badge_color }}">
                {{ $os->categoriaZeladoria->nome }}
            </span>
            @endif
            <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold
                {{ in_array($os->prioridade, ['urgente', 'emergencial']) ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' }}">
                Prioridade: {{ ucfirst($os->prioridade) }}
            </span>
            <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold
                {{ str_contains($os->status, 'Concluída') ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300' : (str_contains($os->status, 'Execução') ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300') }}">
                {{ $os->status }}
            </span>
        </div>
    </div>

    <!-- Ações Rápidas -->
    <div class="flex flex-wrap items-center gap-2">
        @if(!str_contains($os->status, 'Concluída'))
        <button type="button" onclick="abrirModalConcluir()" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-emerald-700 transition">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Concluir & Atestar Serviço
        </button>
        @else
        <div class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-3.5 py-2 text-xs font-semibold text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Serviço Atestado e Concluído
        </div>
        @endif
        <a href="{{ route('ordens-servico.show', $os) }}" class="rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" title="Ver ficha técnica de OS completa">
            Ficha Completa
        </a>
    </div>
</div>

@if(session('success'))
<div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-sm font-medium text-green-800 dark:border-green-800 dark:bg-green-900/30 dark:text-green-300">
    {{ session('success') }}
</div>
@endif

<!-- Banner de Integração com o Protocolo Digital (Módulo A) -->
@if($os->processo)
<div class="mb-6 rounded-xl border border-blue-200 bg-blue-50/70 p-4 dark:border-blue-800 dark:bg-blue-900/20 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div class="flex items-center gap-3">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-600 text-white font-bold">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <div>
            <h4 class="text-sm font-bold text-blue-900 dark:text-blue-200">Vinculada ao Protocolo Digital nº {{ $os->processo->numero }}</h4>
            <p class="text-xs text-blue-700 dark:text-blue-300">Esta ordem de serviço foi originada a partir de um processo administrativo oficial.</p>
        </div>
    </div>
    <a href="{{ route('protocolo.show', $os->processo) }}" class="inline-flex items-center gap-1 text-xs font-bold text-blue-700 hover:underline dark:text-blue-300">
        Abrir Processo no Protocolo &rarr;
    </a>
</div>
@endif

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <!-- Coluna 1 & 2: Localização, Mapa e Fotos Antes/Depois -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Card: Localização e Mapa -->
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
            <h2 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Local da Ocorrência</h2>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">{{ $os->endereco_completo }}</h3>
            @if($os->referencia_local)
            <p class="text-xs text-gray-500 mb-4">Referência: {{ $os->referencia_local }}</p>
            @endif

            @if($os->tem_coordenadas)
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
            <div id="mapaOcorrencia" class="h-64 w-full rounded-lg border border-gray-200 dark:border-gray-700 z-10 mt-3"></div>
            @else
            <div class="rounded-lg border border-dashed border-gray-300 p-6 text-center text-xs text-gray-400 dark:border-gray-700">
                Coordenadas geográficas não registradas para esta ocorrência.
            </div>
            @endif
        </div>

        <!-- Card: Comprovação Fotográfica (Antes vs Depois) -->
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
            <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <svg class="h-5 w-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Registro Fotográfico: Antes & Depois
            </h2>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Foto Antes -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">1. Situação Antes da Execução</h3>
                    @if($os->foto_antes)
                    <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50">
                        <img src="{{ asset('storage/' . $os->foto_antes) }}" alt="Foto Antes" class="h-56 w-full object-cover">
                    </div>
                    @else
                    <div class="flex h-56 flex-col items-center justify-center rounded-lg border border-dashed border-gray-300 bg-gray-50 text-xs text-gray-400 dark:border-gray-700 dark:bg-gray-900/40">
                        <svg class="h-8 w-8 text-gray-300 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Nenhuma foto inicial enviada
                    </div>
                    @endif
                </div>

                <!-- Foto Depois -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-600 mb-2">2. Situação Concluída (Depois)</h3>
                    @if($os->foto_depois)
                    <div class="overflow-hidden rounded-lg border border-emerald-300 dark:border-emerald-800 bg-emerald-50/20">
                        <img src="{{ asset('storage/' . $os->foto_depois) }}" alt="Foto Depois" class="h-56 w-full object-cover">
                    </div>
                    @else
                    <div class="flex h-56 flex-col items-center justify-center rounded-lg border border-dashed border-gray-300 bg-gray-50 text-xs text-gray-400 dark:border-gray-700 dark:bg-gray-900/40">
                        <svg class="h-8 w-8 text-gray-300 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Aguardando conclusão do serviço
                    </div>
                    @endif
                </div>
            </div>

            <!-- Dados do Atesto de Execução -->
            @if($os->atesto_responsavel)
            <div class="mt-6 rounded-lg bg-emerald-50/60 p-4 border border-emerald-200 dark:border-emerald-900/40 dark:bg-emerald-900/20 text-xs">
                <div class="font-bold text-emerald-900 dark:text-emerald-200 mb-1">Atesto Oficial de Conclusão do Serviço Público:</div>
                <div class="text-emerald-800 dark:text-emerald-300">
                    Atestado por <span class="font-bold">{{ $os->atesto_responsavel }}</span> ({{ $os->atesto_cargo ?: 'Fiscal de Obras / Serviços' }}) em {{ $os->atesto_em ? $os->atesto_em->format('d/m/Y H:i') : '—' }}.
                </div>
                @if($os->servicos_realizados)
                <div class="mt-2 text-gray-700 dark:text-gray-300 font-normal">
                    <span class="font-semibold text-gray-800 dark:text-gray-200">Serviços executados:</span> {{ $os->servicos_realizados }}
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>

    <!-- Coluna 3: Detalhes, Solicitante & Secretaria -->
    <div class="space-y-6">
        <!-- Card Cidadão Solicitante -->
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
            <h2 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Cidadão / Solicitante</h2>
            @if($os->cliente)
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-100 text-brand-700 font-bold">
                    {{ substr($os->cliente->nome, 0, 2) }}
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-gray-900 dark:text-white truncate">{{ $os->cliente->nome }}</h3>
                    <p class="text-xs text-gray-500">{{ $os->cliente->documento_principal ?? 'Sem Doc' }}</p>
                </div>
            </div>
            <div class="mt-4 border-t border-gray-100 pt-3 text-xs space-y-1.5 text-gray-600 dark:border-gray-700 dark:text-gray-300">
                <div>Telefone: {{ $os->cliente->telefone ?? $os->cliente->celular ?? 'Não informado' }}</div>
                <div>Canal do chamado: <span class="font-semibold uppercase text-brand-600">{{ $os->origem_demanda }}</span></div>
            </div>
            @else
            <p class="text-xs text-gray-400 italic">Munícipe anônimo.</p>
            @endif
        </div>

        <!-- Card Secretaria Responsável -->
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
            <h2 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Secretaria Executora</h2>
            <div class="rounded-lg bg-blue-50/60 p-3.5 border border-blue-100 dark:border-blue-900/40 dark:bg-blue-900/20">
                <div class="text-sm font-bold text-blue-900 dark:text-blue-300">
                    {{ $os->secretariaResponsavel ? $os->secretariaResponsavel->nome_completo : 'Secretaria de Obras e Serviços Públicos' }}
                </div>
                <div class="mt-2 text-xs text-blue-700 dark:text-blue-300/80">
                    Previsão de conclusão: <span class="font-semibold">{{ $os->prevista_conclusao ? $os->prevista_conclusao->format('d/m/Y') : '7 dias' }}</span>
                </div>
            </div>
        </div>

        <!-- Card Relato da Demanda -->
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
            <h2 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Relato da Demanda</h2>
            <div class="rounded-lg bg-gray-50 p-3 text-xs text-gray-700 dark:bg-gray-900/50 dark:text-gray-300">
                {{ $os->relato_cliente ?: 'Sem relato adicional' }}
            </div>
        </div>
    </div>
</div>

<!-- Modal: Conclusão da Demanda com Foto do "Depois" e Atesto -->
<div id="modalConcluirZeladoria" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-2xl dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Concluir Demanda & Atestar Serviço</h3>
        <p class="text-xs text-gray-500 mb-4">Envie a fotografia comprobatória da obra/serviço finalizado e informe o servidor responsável pelo atesto técnico.</p>

        <form action="{{ route('zeladoria.concluir', $os) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Fotografia da Obra Concluída ("Depois") <span class="text-red-500">*</span></label>
                <input type="file" name="foto_depois" required accept="image/*" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                <p class="mt-1 text-[11px] text-gray-400">Essa foto comprovará o atendimento e ficará disponível no histórico de transparência.</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Serviços Executados em Campo <span class="text-red-500">*</span></label>
                <textarea name="servicos_realizados" rows="3" required placeholder="Ex: Substituída lâmpada de vapor por LED 150W; Aplicada massa asfáltica e compactada..." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white"></textarea>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Nome do Fiscal / Encarregado <span class="text-red-500">*</span></label>
                    <input type="text" name="atesto_responsavel" value="{{ auth()->user()->name }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Cargo / Função</label>
                    <input type="text" name="atesto_cargo" value="Fiscal de Obras / Encarregado" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="fecharModalConcluir()" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300">Cancelar</button>
                <button type="submit" class="rounded-lg bg-emerald-600 px-5 py-2 text-sm font-bold text-white hover:bg-emerald-700">Atestar & Concluir</button>
            </div>
        </form>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
function abrirModalConcluir() {
    var m = document.getElementById('modalConcluirZeladoria');
    m.classList.remove('hidden');
    m.classList.add('flex');
}
function fecharModalConcluir() {
    var m = document.getElementById('modalConcluirZeladoria');
    m.classList.remove('flex');
    m.classList.add('hidden');
}

@if($os->tem_coordenadas)
document.addEventListener('DOMContentLoaded', function () {
    var lat = {{ $os->latitude }};
    var lng = {{ $os->longitude }};
    var map = L.map('mapaOcorrencia').setView([lat, lng], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    var marker = L.marker([lat, lng]).addTo(map);
    marker.bindPopup('<b>{{ $os->codigo_interno }}</b><br>{{ $os->logradouro }}').openPopup();
});
@endif
</script>
@endsection
