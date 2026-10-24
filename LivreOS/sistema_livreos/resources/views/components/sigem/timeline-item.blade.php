@props(['destaque' => false, 'ultimo' => false])
<li class="flex gap-3.5">
    <div aria-hidden="true" class="flex flex-col items-center">
        <span class="mt-1.5 h-2.5 w-2.5 rounded-full {{ $destaque ? 'bg-sigem-primary' : 'bg-gray-400 dark:bg-gray-600' }}"></span>
        @unless($ultimo)<span class="w-0.5 flex-1 bg-sigem-line dark:bg-gray-800"></span>@endunless
    </div>
    <div class="min-w-0 flex-1 {{ $ultimo ? '' : 'pb-5' }}">{{ $slot }}</div>
</li>
