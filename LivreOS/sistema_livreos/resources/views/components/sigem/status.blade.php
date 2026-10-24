{{-- Badge de situação. tom: info | ok | warn | danger | neutral --}}
@props(['tom' => 'info'])
<span {{ $attributes->merge(['class' => 'sigem-badge sigem-badge-' . (in_array($tom, ['info', 'ok', 'warn', 'danger', 'neutral'], true) ? $tom : 'neutral')]) }}>{{ $slot }}</span>
