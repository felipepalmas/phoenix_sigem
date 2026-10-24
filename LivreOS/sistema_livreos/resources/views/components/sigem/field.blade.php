{{-- Campo de formulário: rótulo + controle (slot) + ajuda + erro de validação. --}}
@props(['label', 'for', 'help' => null, 'required' => false, 'error' => null])
@php $erro = $error ?? ($errors->first($for) ?: null); @endphp
<div {{ $attributes->merge(['class' => 'min-w-0']) }}>
    <label for="{{ $for }}" class="sigem-label">{{ $label }}@if($required)<span class="text-sigem-danger" aria-hidden="true"> *</span>@endif</label>
    {{ $slot }}
    @if($erro)
        <p class="sigem-error" id="{{ $for }}-erro">{{ $erro }}</p>
    @elseif($help)
        <p class="sigem-help">{{ $help }}</p>
    @endif
</div>
