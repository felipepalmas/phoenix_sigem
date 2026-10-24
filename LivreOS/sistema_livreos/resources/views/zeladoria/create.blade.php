{{--
 * Componente da aplicação Phoenix - SIGEM
 * Módulo B: Zeladoria Urbana e Serviços Públicos Municipais
 * Abertura de Demanda com Geolocalização e Foto Inicial
 *
 * @license AGPL-3.0
 --}}

@extends('layouts.app')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
            <a href="{{ route('zeladoria.index') }}" class="hover:underline">Zeladoria Municipal</a>
            <span>/</span>
            <span>Nova Demanda</span>
        </div>
        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Registrar Demanda de Zeladoria Urbana</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">Abertura de chamado de campo para manutenção de vias, praças, iluminação ou prédios públicos</p>
    </div>
    <a href="{{ route('zeladoria.index') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
        Voltar ao Painel
    </a>
</div>

@if ($errors->any())
<div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/30 dark:text-red-300">
    <p class="font-bold mb-1">Por favor, corrija os erros abaixo:</p>
    <ul class="list-disc pl-5 space-y-1">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form action="{{ route('zeladoria.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
    @csrf

    <!-- Card 1: Requerente & Classificação do Serviço -->
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-100 text-xs font-bold text-brand-700 dark:bg-brand-900 dark:text-brand-300">1</span>
            Classificação do Serviço & Solicitante
        </h2>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <label for="cliente_id" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Cidadão / Solicitante <span class="text-red-500">*</span>
                </label>
                <select id="cliente_id" name="cliente_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    <option value="">-- Selecione o Cidadão --</option>
                    @foreach($cidadaos as $c)
                    <option value="{{ $c->id }}" {{ old('cliente_id') == $c->id ? 'selected' : '' }}>
                        {{ $c->nome }} ({{ $c->documento_principal ?? 'Sem Doc' }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="categoria_zeladoria_id" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Tipo de Serviço Público / Zeladoria <span class="text-red-500">*</span>
                </label>
                <select id="categoria_zeladoria_id" name="categoria_zeladoria_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    <option value="">-- Selecione a Categoria --</option>
                    @foreach($categorias as $cat)
                    <option value="{{ $cat->id }}" {{ old('categoria_zeladoria_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->nome }} (Prazo: {{ $cat->prazo_padrao_dias }} dias)
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="secretaria_responsavel_id" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Secretaria Executora
                </label>
                <select id="secretaria_responsavel_id" name="secretaria_responsavel_id" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    <option value="">-- Automático (Conforme Categoria) --</option>
                    @foreach($secretarias as $sec)
                    <option value="{{ $sec->id }}" {{ old('secretaria_responsavel_id') == $sec->id ? 'selected' : '' }}>
                        {{ $sec->nome_completo }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="prioridade" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Grau de Prioridade <span class="text-red-500">*</span>
                </label>
                <select id="prioridade" name="prioridade" required class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    <option value="normal" {{ old('prioridade', 'normal') === 'normal' ? 'selected' : '' }}>Normal (Fluxo Padrão)</option>
                    <option value="urgente" {{ old('prioridade') === 'urgente' ? 'selected' : '' }}>Urgente (Risco Moderado)</option>
                    <option value="emergencial" {{ old('prioridade') === 'emergencial' ? 'selected' : '' }}>Emergencial (Risco à Vida / Interdição)</option>
                    <option value="baixa" {{ old('prioridade') === 'baixa' ? 'selected' : '' }}>Baixa</option>
                </select>
            </div>

            <div>
                <label for="origem_demanda" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Canal / Origem do Chamado <span class="text-red-500">*</span>
                </label>
                <select id="origem_demanda" name="origem_demanda" required class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    <option value="balcao" {{ old('origem_demanda', 'balcao') === 'balcao' ? 'selected' : '' }}>Presencial / Balcão</option>
                    <option value="telefone_156" {{ old('origem_demanda') === 'telefone_156' ? 'selected' : '' }}>Central Telefônica / 156</option>
                    <option value="ouvidoria" {{ old('origem_demanda') === 'ouvidoria' ? 'selected' : '' }}>Ouvidoria Geral</option>
                    <option value="fiscalizacao" {{ old('origem_demanda') === 'fiscalizacao' ? 'selected' : '' }}>Fiscalização Interna / Ronda</option>
                    <option value="portal" {{ old('origem_demanda') === 'portal' ? 'selected' : '' }}>Portal do Cidadão</option>
                </select>
            </div>

            <div>
                <label for="processo_id" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Vincular a Protocolo Digital (Zero Papel)
                </label>
                <select id="processo_id" name="processo_id" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    <option value="">-- Sem vínculo direto --</option>
                    @foreach($processos as $proc)
                    <option value="{{ $proc->id }}" {{ old('processo_id') == $proc->id ? 'selected' : '' }}>
                        Protocolo {{ $proc->numero }} - {{ Str::limit($proc->assunto, 35) }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Card 2: Endereço & Geolocalização (Clique no Mapa) -->
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-100 text-xs font-bold text-brand-700 dark:bg-brand-900 dark:text-brand-300">2</span>
            Local da Ocorrência & Coordenadas Geográficas
        </h2>
        <p class="text-xs text-gray-500 mb-4">Informe o endereço ou clique diretamente no mapa abaixo para capturar as coordenadas exatas.</p>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-4">
            <div class="lg:col-span-2">
                <label for="logradouro" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Logradouro (Rua / Avenida) <span class="text-red-500">*</span>
                </label>
                <input type="text" id="logradouro" name="logradouro" value="{{ old('logradouro') }}" required placeholder="Ex: Rua das Palmeiras..." class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>

            <div>
                <label for="numero_logradouro" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Número / Km
                </label>
                <input type="text" id="numero_logradouro" name="numero_logradouro" value="{{ old('numero_logradouro') }}" placeholder="Ex: 1420 ou S/N" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>

            <div>
                <label for="bairro" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Bairro <span class="text-red-500">*</span>
                </label>
                <input type="text" id="bairro" name="bairro" value="{{ old('bairro') }}" required placeholder="Ex: Centro, Bairro Novo..." class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>

            <div class="sm:col-span-2">
                <label for="referencia_local" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Ponto de Referência
                </label>
                <input type="text" id="referencia_local" name="referencia_local" value="{{ old('referencia_local') }}" placeholder="Ex: Em frente à Escola Municipal, próximo ao posto de combustível..." class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>

            <div>
                <label for="latitude" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Latitude</label>
                <input type="text" id="latitude" name="latitude" value="{{ old('latitude') }}" readonly class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" placeholder="Clique no mapa">
            </div>

            <div>
                <label for="longitude" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Longitude</label>
                <input type="text" id="longitude" name="longitude" value="{{ old('longitude') }}" readonly class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" placeholder="Clique no mapa">
            </div>
        </div>

        <!-- Seletor no Mapa com Leaflet -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <div id="mapaPicker" class="h-64 w-full rounded-lg border border-gray-200 dark:border-gray-700 z-10"></div>
        <p class="mt-1 text-xs text-gray-500">Dica: navegue e clique no mapa para posicionar o pin no local exato do serviço.</p>
    </div>

    <!-- Card 3: Descrição e Foto Inicial (Antes) -->
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-100 text-xs font-bold text-brand-700 dark:bg-brand-900 dark:text-brand-300">3</span>
            Descrição & Registro Fotográfico Inicial (Antes)
        </h2>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="relato_cliente" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Detalhamento do Problema / Solicitação <span class="text-red-500">*</span>
                </label>
                <textarea id="relato_cliente" name="relato_cliente" rows="4" required placeholder="Descreva a extensão do problema (ex: buraco profundo no asfalto com risco de acidentes, lâmpada piscando há 1 semana)..." class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">{{ old('relato_cliente') }}</textarea>
            </div>

            <div>
                <label for="foto_antes" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Foto do Local / Problema (Situação "Antes")
                </label>
                <input type="file" id="foto_antes" name="foto_antes" accept="image/*" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                <p class="mt-2 text-xs text-gray-500">A imagem servirá como comprovação da demanda e será comparada com a foto de conclusão da equipe em campo.</p>
            </div>
        </div>
    </div>

    <!-- Botões -->
    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('zeladoria.index') }}" class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
            Cancelar
        </a>
        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow hover:bg-brand-700 transition">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Registrar Demanda e Emitir OS
        </button>
    </div>
</form>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var latInput = document.getElementById('latitude');
    var lngInput = document.getElementById('longitude');

    var initialLat = latInput.value ? parseFloat(latInput.value) : -15.793889;
    var initialLng = lngInput.value ? parseFloat(lngInput.value) : -47.882778;
    var zoom = latInput.value ? 15 : 4;

    var mapPicker = L.map('mapaPicker').setView([initialLat, initialLng], zoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(mapPicker);

    var marker = null;
    if (latInput.value && lngInput.value) {
        marker = L.marker([initialLat, initialLng]).addTo(mapPicker);
    }

    mapPicker.on('click', function (e) {
        var lat = e.latlng.lat.toFixed(7);
        var lng = e.latlng.lng.toFixed(7);

        latInput.value = lat;
        lngInput.value = lng;

        if (marker) {
            marker.setLatLng(e.latlng);
        } else {
            marker = L.marker(e.latlng).addTo(mapPicker);
        }
    });
});
</script>
@endsection
