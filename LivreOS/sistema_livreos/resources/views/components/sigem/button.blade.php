{{-- Botão/link SIGEM. variant: primary | secondary | danger | ghost; size: md | sm --}}
@props(['variant' => 'secondary', 'href' => null, 'size' => 'md', 'type' => 'button'])
@php
    $classes = 'sigem-btn sigem-btn-' . $variant . ($size === 'sm' ? ' sigem-btn-sm' : '');
@endphp
@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
