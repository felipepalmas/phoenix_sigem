{{--
 * Componente da aplicação Phoenix - SIGEM (base LivreOS)
 * Menu lateral no padrão SIGEM Clean: grupos por finalidade, links diretos,
 * ícone próprio por módulo. As páginas internas de cada módulo são abas no topo.
 *
 * @license AGPL-3.0
 --}}
@php
    use App\Helpers\MenuHelper;
    use App\Models\Empresa;

    $menuGroups = MenuHelper::getMenuGroups();
    $menuRodape = MenuHelper::getFooterItems();

    $empresa = Empresa::first();
    $orgaoNome = $empresa?->nome ?: $empresa?->razao_social ?: 'Prefeitura Municipal';
    $empresaLogoUrl = null;
    if ($empresa?->logo_path) {
        $logoPath = storage_path('app/public/' . $empresa->logo_path);
        if (file_exists($logoPath)) {
            $mime = @mime_content_type($logoPath) ?: 'image/png';
            $empresaLogoUrl = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
        }
    }
    $mostrarTexto = "\$store.sidebar.isExpanded || \$store.sidebar.isHovered || \$store.sidebar.isMobileOpen";
@endphp

<aside id="sidebar" aria-label="Menu principal"
    class="fixed top-0 bottom-0 left-0 z-[100000] flex flex-col overflow-y-auto overflow-x-hidden overscroll-contain border-r border-sigem-line bg-white px-3 text-sigem-ink transition-all duration-200 ease-in-out dark:border-gray-800 dark:bg-gray-900 dark:text-gray-100 xl:z-[99999]"
    x-data="{ abertos: {} }"
    :class="{
        'w-[248px]': {{ $mostrarTexto }},
        'w-[72px]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered,
        'translate-x-0': $store.sidebar.isMobileOpen,
        '-translate-x-full xl:translate-x-0': !$store.sidebar.isMobileOpen
    }"
    @mouseenter="if (!$store.sidebar.isExpanded) $store.sidebar.setHovered(true)"
    @mouseleave="$store.sidebar.setHovered(false)">

    <div class="absolute top-3 right-3 xl:hidden">
        <button type="button" data-mobile-menu-close
            onclick="document.dispatchEvent(new CustomEvent('close-mobile-menu'))"
            class="flex h-10 w-10 items-center justify-center rounded-md text-sigem-muted hover:bg-sigem-neutral-soft dark:text-gray-400 dark:hover:bg-gray-800"
            aria-label="Fechar menu">
            <svg aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    {{-- Identificação --}}
    <a href="/" class="flex items-center gap-2.5 px-2 pb-4 pt-5 no-underline">
        <img src="{{ $empresaLogoUrl ?: asset(config('app.icon_path', 'logo/icone.png')) }}" alt="" class="h-8 w-8 shrink-0 rounded-md object-contain" width="32" height="32">
        <span x-show="{{ $mostrarTexto }}" class="flex min-w-0 flex-col leading-tight">
            <strong class="truncate text-sm text-sigem-ink dark:text-white">Phoenix SIGEM</strong>
            <span class="truncate text-xs text-sigem-muted dark:text-gray-400">{{ $orgaoNome }}</span>
        </span>
    </a>

    <nav class="flex min-h-0 flex-1 flex-col">
        @foreach ($menuGroups as $gi => $grupo)
            @if(!empty($grupo['title']))
                <div class="px-2.5 pb-1 pt-4 text-xs text-sigem-muted dark:text-gray-500" x-show="{{ $mostrarTexto }}">{{ $grupo['title'] }}</div>
                <div class="mx-2.5 my-3 border-t border-sigem-line dark:border-gray-800" x-show="!({{ $mostrarTexto }})" aria-hidden="true"></div>
            @endif
            <ul class="m-0 flex list-none flex-col gap-0.5 p-0">
                @foreach ($grupo['items'] as $ii => $item)
                    @php
                        $ativo = MenuHelper::itemAtivo($item);
                        $chave = $gi . '-' . $ii;
                    @endphp
                    <li>
                        @if (!empty($item['subItems']))
                            <button type="button"
                                x-init="abertos['{{ $chave }}'] = {{ $ativo ? 'true' : 'false' }}"
                                @click="abertos['{{ $chave }}'] = !abertos['{{ $chave }}']"
                                :aria-expanded="abertos['{{ $chave }}'] ? 'true' : 'false'"
                                title="{{ $item['name'] }}"
                                class="flex w-full items-center gap-2.5 rounded-md px-2.5 py-2 text-left text-sm {{ $ativo ? 'font-semibold text-sigem-primary dark:text-brand-300' : 'text-sigem-ink hover:bg-sigem-neutral-soft dark:text-gray-200 dark:hover:bg-white/5' }}">
                                <span class="shrink-0">{!! MenuHelper::getIconSvg($item['icon'] ?? 'config') !!}</span>
                                <span class="flex-1 truncate" x-show="{{ $mostrarTexto }}">{{ $item['name'] }}</span>
                                <svg x-show="{{ $mostrarTexto }}" aria-hidden="true" class="h-4 w-4 shrink-0 text-sigem-muted transition-transform" :class="abertos['{{ $chave }}'] && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <ul x-show="abertos['{{ $chave }}'] && ({{ $mostrarTexto }})" x-cloak class="m-0 mb-1 ml-[38px] mt-0.5 flex list-none flex-col gap-0.5 border-l border-sigem-line p-0 pl-2 dark:border-gray-800">
                                @foreach ($item['subItems'] as $sub)
                                    @php $subAtivo = MenuHelper::isActive($sub['path']); @endphp
                                    <li>
                                        <a href="{{ $sub['path'] }}" @if($subAtivo) aria-current="page" @endif
                                            class="block rounded-md px-2.5 py-1.5 text-[13px] no-underline {{ $subAtivo ? 'bg-sigem-primary-soft font-semibold text-sigem-primary dark:bg-brand-500/15 dark:text-brand-300' : 'text-sigem-ink hover:bg-sigem-neutral-soft dark:text-gray-300 dark:hover:bg-white/5' }}">{{ $sub['name'] }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <a href="{{ $item['path'] }}" title="{{ $item['name'] }}" @if($ativo) aria-current="page" @endif
                                class="flex items-center gap-2.5 rounded-md px-2.5 py-2 text-sm no-underline {{ $ativo ? 'bg-sigem-primary-soft font-semibold text-sigem-primary dark:bg-brand-500/15 dark:text-brand-300' : 'text-sigem-ink hover:bg-sigem-neutral-soft dark:text-gray-200 dark:hover:bg-white/5' }}">
                                <span class="shrink-0">{!! MenuHelper::getIconSvg($item['icon'] ?? 'materiais') !!}</span>
                                <span class="flex-1 truncate" x-show="{{ $mostrarTexto }}">{{ $item['name'] }}</span>
                                @if(!empty($item['badge']))
                                    <span x-show="{{ $mostrarTexto }}" class="rounded-full bg-sigem-primary px-1.5 text-xs font-semibold text-white">{{ $item['badge'] }}</span>
                                @endif
                            </a>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endforeach

        <div class="flex-1"></div>

        <ul class="m-0 mb-3 mt-4 flex list-none flex-col gap-0.5 border-t border-sigem-line p-0 pt-3 dark:border-gray-800">
            @foreach($menuRodape as $item)
                @php $ativo = MenuHelper::itemAtivo($item); @endphp
                <li>
                    <a href="{{ $item['path'] }}" title="{{ $item['name'] }}" @if(!empty($item['external'])) target="_blank" rel="noopener" @endif
                        class="flex items-center gap-2.5 rounded-md px-2.5 py-2 text-sm no-underline {{ $ativo ? 'bg-sigem-primary-soft font-semibold text-sigem-primary' : 'text-sigem-muted hover:bg-sigem-neutral-soft hover:text-sigem-ink dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-200' }}">
                        <span class="shrink-0">{!! MenuHelper::getIconSvg($item['icon']) !!}</span>
                        <span class="flex-1 truncate" x-show="{{ $mostrarTexto }}">{{ $item['name'] }}@if(!empty($item['external']))<span class="sr-only"> (abre em nova aba)</span>@endif</span>
                    </a>
                </li>
            @endforeach
        </ul>

        @php $sidebarWidget = function_exists('apply_filters') ? apply_filters('sidebar.widget', null) : null; @endphp
        @if($sidebarWidget)
            <div x-show="{{ $mostrarTexto }}" class="mb-3">{!! $sidebarWidget !!}</div>
        @endif
    </nav>
</aside>
