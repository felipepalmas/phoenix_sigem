{{--
 * Componente da aplicação Phoenix - SIGEM
 * Zeladoria — registro de nova demanda com marcação no mapa (padrão SIGEM Clean)
 *
 * @license AGPL-3.0
 --}}
@extends('layouts.app')

@section('content')
<x-sigem.page-header title="Nova demanda de zeladoria" subtitle="Registre o pedido recebido no balcão, telefone 156, ouvidoria ou fiscalização."
    :breadcrumbs="['Zeladoria' => route('zeladoria.index'), 'Nova demanda' => null]" />

<x-sigem.flash />

<form method="POST" action="{{ route('zeladoria.store') }}" enctype="multipart/form-data" class="flex max-w-5xl flex-col gap-5">
    @csrf

    <x-sigem.card title="O que é e quem pediu" id="t-oque">
        <div class="grid gap-4 md:grid-cols-2">
            <x-sigem.field label="Serviço" for="categoria_zeladoria_id" required>
                <select id="categoria_zeladoria_id" name="categoria_zeladoria_id" required class="sigem-input">
                    <option value="">Selecione o serviço</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat->id }}" @selected((string) old('categoria_zeladoria_id') === (string) $cat->id)>{{ $cat->nome }}</option>
                    @endforeach
                </select>
            </x-sigem.field>
            <x-sigem.field label="Solicitante" for="cliente_id" required help="Cidadão já cadastrado.">
                <select id="cliente_id" name="cliente_id" required class="sigem-input">
                    <option value="">Selecione o cidadão</option>
                    @foreach($cidadaos as $c)
                        <option value="{{ $c->id }}" @selected((string) old('cliente_id') === (string) $c->id)>{{ $c->nome ?: $c->razao_social }}</option>
                    @endforeach
                </select>
            </x-sigem.field>
            <x-sigem.field label="Como chegou" for="origem_demanda" required>
                <select id="origem_demanda" name="origem_demanda" required class="sigem-input">
                    @foreach(['balcao' => 'Balcão (presencial)', 'telefone_156' => 'Telefone 156', 'ouvidoria' => 'Ouvidoria', 'fiscalizacao' => 'Fiscalização / ronda', 'portal' => 'Portal do Cidadão'] as $v => $r)
                        <option value="{{ $v }}" @selected(old('origem_demanda', 'balcao') === $v)>{{ $r }}</option>
                    @endforeach
                </select>
            </x-sigem.field>
            <x-sigem.field label="Prioridade" for="prioridade" required>
                <select id="prioridade" name="prioridade" required class="sigem-input">
                    @foreach(['normal' => 'Normal', 'urgente' => 'Urgente (risco moderado)', 'emergencial' => 'Emergencial (risco à vida ou interdição)', 'baixa' => 'Baixa'] as $v => $r)
                        <option value="{{ $v }}" @selected(old('prioridade', 'normal') === $v)>{{ $r }}</option>
                    @endforeach
                </select>
            </x-sigem.field>
            <x-sigem.field label="Relato" for="relato_cliente" required class="md:col-span-2" help="Descreva o problema como foi informado.">
                <textarea id="relato_cliente" name="relato_cliente" rows="3" required minlength="5" class="sigem-input">{{ old('relato_cliente') }}</textarea>
            </x-sigem.field>
        </div>
    </x-sigem.card>

    <x-sigem.card title="Onde" id="t-onde" subtitle="Preencha o endereço e, se possível, clique no mapa para marcar o ponto exato.">
        <div class="grid gap-4 md:grid-cols-6">
            <x-sigem.field label="Rua / logradouro" for="logradouro" required class="md:col-span-4">
                <input id="logradouro" name="logradouro" type="text" required maxlength="255" value="{{ old('logradouro') }}" class="sigem-input">
            </x-sigem.field>
            <x-sigem.field label="Número" for="numero_logradouro" class="md:col-span-2">
                <input id="numero_logradouro" name="numero_logradouro" type="text" maxlength="30" value="{{ old('numero_logradouro') }}" placeholder="S/N" class="sigem-input">
            </x-sigem.field>
            <x-sigem.field label="Bairro" for="bairro" required class="md:col-span-3">
                <input id="bairro" name="bairro" type="text" required maxlength="100" value="{{ old('bairro') }}" class="sigem-input">
            </x-sigem.field>
            <x-sigem.field label="Ponto de referência" for="referencia_local" class="md:col-span-3">
                <input id="referencia_local" name="referencia_local" type="text" maxlength="255" value="{{ old('referencia_local') }}" class="sigem-input">
            </x-sigem.field>
        </div>
        <div id="mapaPicker" class="mt-4 h-[300px] w-full rounded-md border border-sigem-line dark:border-gray-800" role="region" aria-label="Mapa para marcar o local"></div>
        <div class="mt-2 flex flex-wrap items-center gap-3 text-[13px] text-sigem-muted dark:text-gray-400">
            <span id="coordTexto">{{ old('latitude') ? 'Ponto marcado: ' . old('latitude') . ', ' . old('longitude') : 'Nenhum ponto marcado no mapa.' }}</span>
            <button type="button" id="limparPonto" class="sigem-link text-[13px] font-normal">Limpar ponto</button>
        </div>
        <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude') }}">
        <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude') }}">
    </x-sigem.card>

    <x-sigem.card title="Encaminhamento e foto" id="t-enc">
        <div class="grid gap-4 md:grid-cols-2">
            <x-sigem.field label="Secretaria responsável" for="secretaria_responsavel_id" help="Se não escolher, vai para a secretaria padrão do serviço.">
                <select id="secretaria_responsavel_id" name="secretaria_responsavel_id" class="sigem-input">
                    <option value="">Padrão do serviço</option>
                    @foreach($secretarias as $s)
                        <option value="{{ $s->id }}" @selected((string) old('secretaria_responsavel_id') === (string) $s->id)>{{ $s->sigla ? $s->sigla . ' — ' : '' }}{{ $s->nome }}</option>
                    @endforeach
                </select>
            </x-sigem.field>
            <x-sigem.field label="Processo vinculado" for="processo_id" help="Opcional: quando a demanda nasceu de um processo do Protocolo.">
                <select id="processo_id" name="processo_id" class="sigem-input">
                    <option value="">Sem vínculo</option>
                    @foreach($processos as $proc)
                        <option value="{{ $proc->id }}" @selected((string) old('processo_id') === (string) $proc->id)>{{ $proc->numero }} — {{ \Illuminate\Support\Str::limit($proc->assunto, 60) }}</option>
                    @endforeach
                </select>
            </x-sigem.field>
            <x-sigem.field label="Foto do local (antes)" for="foto_antes" class="md:col-span-2" help="Imagem até 15 MB.">
                <input id="foto_antes" name="foto_antes" type="file" accept="image/*" class="sigem-input h-auto py-1.5">
            </x-sigem.field>
        </div>
    </x-sigem.card>

    <div class="flex flex-wrap justify-end gap-2">
        <x-sigem.button href="{{ route('zeladoria.index') }}">Cancelar</x-sigem.button>
        <x-sigem.button type="submit" variant="primary">Registrar demanda</x-sigem.button>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.sigemMapa) return;
    var lat = document.getElementById('latitude'), lng = document.getElementById('longitude'), texto = document.getElementById('coordTexto');
    var temPonto = lat.value && lng.value;
    var mapa = window.sigemMapa('mapaPicker', temPonto ? { centro: [parseFloat(lat.value), parseFloat(lng.value)], zoom: 17 } : {});
    if (!mapa) return;
    var marcador = temPonto ? L.marker([parseFloat(lat.value), parseFloat(lng.value)]).addTo(mapa) : null;
    mapa.on('click', function (e) {
        lat.value = e.latlng.lat.toFixed(7);
        lng.value = e.latlng.lng.toFixed(7);
        texto.textContent = 'Ponto marcado: ' + lat.value + ', ' + lng.value;
        if (marcador) marcador.setLatLng(e.latlng); else marcador = L.marker(e.latlng).addTo(mapa);
    });
    document.getElementById('limparPonto').addEventListener('click', function () {
        lat.value = ''; lng.value = '';
        texto.textContent = 'Nenhum ponto marcado no mapa.';
        if (marcador) { mapa.removeLayer(marcador); marcador = null; }
    });
});
</script>
@endpush
