{{-- Estado vazio: o que não há e o que fazer a seguir. --}}
@props(['title', 'description' => null])
<div {{ $attributes->merge(['class' => 'flex flex-col items-center gap-2 px-6 py-10 text-center']) }}>
    <svg aria-hidden="true" class="h-9 w-9 text-gray-300 dark:text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 13l3-8h12l3 8v6a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1z"/><path d="M3 13h5l1 3h6l1-3h5"/></svg>
    <p class="m-0 font-semibold text-sigem-ink dark:text-white">{{ $title }}</p>
    @if($description)<p class="m-0 max-w-md text-[13px] text-sigem-muted dark:text-gray-400">{{ $description }}</p>@endif
    @if(trim($slot) !== '')<div class="mt-2">{{ $slot }}</div>@endif
</div>
