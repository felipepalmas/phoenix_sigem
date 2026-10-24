{{--
 * Componente da aplicação Phoenix - SIGEM
 * Portal do Cidadão — pedir um serviço (padrão SIGEM Clean, linguagem simples)
 *
 * @license AGPL-3.0
 --}}
@extends('layouts.portal')

@section('content')
<div class="mx-auto max-w-3xl">
    <x-sigem.page-header title="Pedir um serviço" subtitle="Leva cerca de 2 minutos. Você recebe um código para acompanhar o andamento." />
    <x-sigem.flash />

    <form action="{{ route('portal.zeladoria.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-5">
        @csrf
        <x-sigem.card title="1. O que precisa ser feito" id="p1">
            <div class="flex flex-col gap-4">
                <fieldset class="m-0 border-0 p-0">
                    <legend class="sigem-label">Tipo de serviço <span class="text-sigem-danger" aria-hidden="true">*</span></legend>
                    <div class="grid gap-2 sm:grid-cols-2">
                        @php $escolhida = (string) old('categoria_zeladoria_id', request('categoria')); @endphp
                        @foreach($categorias as $cat)
                            <label class="flex cursor-pointer items-center gap-2.5 rounded-md border border-sigem-field px-3 py-2.5 text-sm has-[:checked]:border-sigem-primary has-[:checked]:bg-sigem-primary-soft dark:border-gray-700">
                                <input type="radio" name="categoria_zeladoria_id" value="{{ $cat->id }}" required @checked($escolhida === (string) $cat->id)>
                                {{ $cat->nome }}
                            </label>
                        @endforeach
                    </div>
                    @error('categoria_zeladoria_id')<p class="sigem-error">{{ $message }}</p>@enderror
                </fieldset>
                <x-sigem.field label="Descreva o problema" for="relato_cliente" required help="Ex.: poste em frente ao número 120 está apagado há 3 noites.">
                    <textarea id="relato_cliente" name="relato_cliente" rows="3" required minlength="10" class="sigem-input">{{ old('relato_cliente') }}</textarea>
                </x-sigem.field>
                <x-sigem.field label="Foto (opcional)" for="foto" help="Ajuda a equipe a encontrar e avaliar o problema. Até 10 MB.">
                    <input id="foto" name="foto" type="file" accept="image/*" capture="environment" class="sigem-input h-auto py-1.5">
                </x-sigem.field>
            </div>
        </x-sigem.card>

        <x-sigem.card title="2. Onde fica" id="p2">
            <div class="grid gap-4 sm:grid-cols-6">
                <x-sigem.field label="Rua" for="logradouro" required class="sm:col-span-4">
                    <input id="logradouro" name="logradouro" type="text" required maxlength="200" value="{{ old('logradouro') }}" class="sigem-input" autocomplete="address-line1">
                </x-sigem.field>
                <x-sigem.field label="Número" for="numero" class="sm:col-span-2">
                    <input id="numero" name="numero" type="text" maxlength="20" value="{{ old('numero') }}" placeholder="S/N" class="sigem-input">
                </x-sigem.field>
                <x-sigem.field label="Bairro" for="bairro" required class="sm:col-span-3">
                    <input id="bairro" name="bairro" type="text" required maxlength="100" value="{{ old('bairro') }}" class="sigem-input">
                </x-sigem.field>
                <x-sigem.field label="Ponto de referência" for="ponto_referencia" class="sm:col-span-3">
                    <input id="ponto_referencia" name="ponto_referencia" type="text" maxlength="200" value="{{ old('ponto_referencia') }}" placeholder="Ex.: ao lado da escola" class="sigem-input">
                </x-sigem.field>
            </div>
            <p class="m-0 mt-4 text-[13px] text-sigem-muted">Se quiser, toque no mapa para marcar o local exato.</p>
            <div id="mapaCidadao" class="mt-2 h-[260px] w-full rounded-md border border-sigem-line dark:border-gray-800" role="region" aria-label="Mapa para marcar o local (opcional)"></div>
            <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude') }}">
            <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude') }}">
            <button type="button" id="usarLocalizacao" class="sigem-link mt-2 text-[13px] font-normal">Usar minha localização atual</button>
        </x-sigem.card>

        <x-sigem.card title="3. Seus dados" id="p3" subtitle="Usados só para contato sobre este pedido. Na página de acompanhamento, nome e CPF aparecem mascarados.">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-sigem.field label="Nome completo" for="nome" required class="sm:col-span-2">
                    <input id="nome" name="nome" type="text" required maxlength="120" value="{{ old('nome') }}" class="sigem-input" autocomplete="name">
                </x-sigem.field>
                <x-sigem.field label="CPF" for="cpf" required>
                    <input id="cpf" name="cpf" type="text" required maxlength="20" value="{{ old('cpf') }}" inputmode="numeric" placeholder="000.000.000-00" class="sigem-input">
                </x-sigem.field>
                <x-sigem.field label="Telefone / WhatsApp" for="telefone" required>
                    <input id="telefone" name="telefone" type="tel" required maxlength="25" value="{{ old('telefone') }}" class="sigem-input" autocomplete="tel">
                </x-sigem.field>
                <x-sigem.field label="E-mail (opcional)" for="email" class="sm:col-span-2">
                    <input id="email" name="email" type="email" maxlength="100" value="{{ old('email') }}" class="sigem-input" autocomplete="email">
                </x-sigem.field>
            </div>
        </x-sigem.card>

        <div class="flex justify-end">
            <x-sigem.button type="submit" variant="primary">Enviar pedido</x-sigem.button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.sigemMapa) return;
    var lat = document.getElementById('latitude'), lng = document.getElementById('longitude');
    var mapa = window.sigemMapa('mapaCidadao', lat.value ? { centro: [parseFloat(lat.value), parseFloat(lng.value)], zoom: 17 } : {});
    if (!mapa) return;
    var marcador = lat.value ? L.marker([parseFloat(lat.value), parseFloat(lng.value)]).addTo(mapa) : null;
    function marcar(latlng) {
        lat.value = latlng.lat.toFixed(7); lng.value = latlng.lng.toFixed(7);
        if (marcador) marcador.setLatLng(latlng); else marcador = L.marker(latlng).addTo(mapa);
    }
    mapa.on('click', function (e) { marcar(e.latlng); });
    document.getElementById('usarLocalizacao').addEventListener('click', function () {
        if (!navigator.geolocation) return;
        navigator.geolocation.getCurrentPosition(function (pos) {
            var p = L.latLng(pos.coords.latitude, pos.coords.longitude);
            marcar(p); mapa.setView(p, 17);
        });
    });
});
</script>
@endpush
