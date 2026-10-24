{{-- Mensagens de retorno (success/error/warning/info) e erros de validação. --}}
@php
    $mapa = ['success' => 'ok', 'error' => 'danger', 'warning' => 'warn', 'info' => 'info'];
@endphp
@foreach($mapa as $chave => $tom)
    @if(session($chave))
        <div role="{{ $tom === 'danger' ? 'alert' : 'status' }}" class="sigem-alert sigem-alert-{{ $tom }} mb-4">
            <span>{{ session($chave) }}</span>
        </div>
    @endif
@endforeach
@if($errors->any())
    <div role="alert" class="sigem-alert sigem-alert-danger mb-4">
        <div>
            <strong>Revise os campos destacados:</strong>
            <ul class="m-0 mt-1 list-disc pl-5">
                @foreach($errors->all() as $erro)<li>{{ $erro }}</li>@endforeach
            </ul>
        </div>
    </div>
@endif
