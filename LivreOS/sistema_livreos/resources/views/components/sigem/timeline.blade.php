{{-- Linha do tempo: cada <x-sigem.timeline-item> é um evento. --}}
<ol {{ $attributes->merge(['class' => 'm-0 flex list-none flex-col p-0']) }}>{{ $slot }}</ol>
