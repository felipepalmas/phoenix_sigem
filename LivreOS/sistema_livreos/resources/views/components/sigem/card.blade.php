{{-- Cartão SIGEM com título opcional e ações no cabeçalho. --}}
@props(['title' => null, 'subtitle' => null, 'padded' => true, 'id' => null])
<section {{ $attributes->merge(['class' => 'sigem-card']) }} @if($title && $id) aria-labelledby="{{ $id }}" @endif>
    @if($title || isset($actions))
        <div class="sigem-card-header">
            <div class="min-w-0 flex-1">
                @if($title)<h2 class="sigem-card-title" @if($id) id="{{ $id }}" @endif>{{ $title }}</h2>@endif
                @if($subtitle)<p class="m-0 mt-0.5 text-[13px] text-sigem-muted dark:text-gray-400">{{ $subtitle }}</p>@endif
            </div>
            {{ $actions ?? '' }}
        </div>
    @endif
    @if($padded)
        <div class="sigem-card-body">{{ $slot }}</div>
    @else
        {{ $slot }}
    @endif
</section>
