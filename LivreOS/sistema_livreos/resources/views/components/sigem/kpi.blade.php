{{-- Indicador numérico neutro. hintTone: muted | danger | warn | ok --}}
@props(['label', 'value', 'hint' => null, 'hintTone' => 'muted', 'href' => null])
@php
    $tom = ['muted' => 'text-sigem-muted dark:text-gray-400', 'danger' => 'text-sigem-danger dark:text-red-400', 'warn' => 'text-sigem-warn dark:text-amber-300', 'ok' => 'text-sigem-ok dark:text-green-400'][$hintTone] ?? 'text-sigem-muted';
    $tag = $href ? 'a' : 'div';
@endphp
<{{ $tag }} @if($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => 'sigem-card flex flex-col gap-0.5 px-4 py-3.5 no-underline text-sigem-ink dark:text-white' . ($href ? ' transition-colors hover:border-sigem-field hover:bg-gray-50 dark:hover:bg-white/[0.03]' : '')]) }}>
    <span class="text-[13px] text-sigem-muted dark:text-gray-400">{{ $label }}</span>
    <span class="text-[28px] font-semibold leading-tight">{{ $value }}</span>
    @if($hint)<span class="text-[13px] {{ $tom }}">{{ $hint }}</span>@endif
</{{ $tag }}>
