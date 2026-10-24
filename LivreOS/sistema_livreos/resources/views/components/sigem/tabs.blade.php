{{-- Abas de navegação. items: [['name' => '', 'path' => '', 'active' => bool, 'count' => ?int]] --}}
@props(['items' => [], 'label' => 'Seções'])
@if(count($items))
<nav aria-label="{{ $label }}" {{ $attributes->merge(['class' => 'sigem-tabs']) }}>
    @foreach($items as $aba)
        <a href="{{ $aba['path'] }}" class="sigem-tab {{ !empty($aba['active']) ? 'sigem-tab-active' : '' }}" @if(!empty($aba['active'])) aria-current="page" @endif>
            {{ $aba['name'] }}@isset($aba['count'])<span class="ml-1 font-normal text-sigem-muted dark:text-gray-400">{{ $aba['count'] }}</span>@endisset
        </a>
    @endforeach
</nav>
@endif
