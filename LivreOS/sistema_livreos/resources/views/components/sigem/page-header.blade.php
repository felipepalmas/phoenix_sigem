{{-- Cabeçalho de página SIGEM: título, subtítulo, trilha opcional e ações (no máximo 1 primária). --}}
@props(['title', 'subtitle' => null, 'breadcrumbs' => []])
<div {{ $attributes->merge(['class' => 'mb-5 flex flex-wrap items-end gap-3']) }}>
    <div class="min-w-0 flex-1 basis-72">
        @if(!empty($breadcrumbs))
            <nav aria-label="Trilha" class="mb-1 text-[13px] text-sigem-muted dark:text-gray-400">
                @foreach($breadcrumbs as $rotulo => $url)
                    @if($url)<a href="{{ $url }}" class="sigem-link font-normal">{{ $rotulo }}</a>@else<span>{{ $rotulo }}</span>@endif
                    @if(!$loop->last)<span aria-hidden="true" class="mx-1">/</span>@endif
                @endforeach
            </nav>
        @endif
        <div class="flex flex-wrap items-center gap-2">
            <h1 class="m-0 text-[22px] font-semibold leading-tight text-sigem-ink dark:text-white">{{ $title }}</h1>
            {{ $badges ?? '' }}
        </div>
        @if($subtitle)
            <p class="m-0 mt-1 text-sigem-muted dark:text-gray-400">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
