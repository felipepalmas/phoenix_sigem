{{--
 * Componente da aplicação Phoenix - SIGEM
 * Cabeçalho: menu, busca global (Ctrl K) com resultados instantâneos, tema, avisos e usuário com lotação.
 *
 * @license AGPL-3.0
 --}}
<header
    class="sticky top-0 z-[99999] w-full min-w-0 border-b border-sigem-line bg-white dark:border-gray-800 dark:bg-gray-900"
    x-data="{
        termoBusca: @js(request('q', '')),
        resultados: [],
        carregando: false,
        aberto: false,
        buscaMobile: false,
        timeout: null,
        onInput() {
            clearTimeout(this.timeout);
            const q = (this.termoBusca || '').trim();
            if (q.length < 2) { this.resultados = []; this.aberto = false; return; }
            this.carregando = true;
            this.aberto = true;
            this.timeout = setTimeout(() => {
                fetch('{{ route('busca.global.api') }}?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } })
                    .then(r => r.json())
                    .then(data => { this.resultados = data.sections || []; this.carregando = false; })
                    .catch(() => { this.carregando = false; });
            }, 220);
        },
        submeterBusca() {
            const q = (this.termoBusca || '').trim();
            if (q) window.location.href = '{{ route('busca.global') }}?q=' + encodeURIComponent(q);
        },
        tom(t) {
            return { info: 'sigem-badge-info', danger: 'sigem-badge-danger', warning: 'sigem-badge-warn', success: 'sigem-badge-ok' }[t] || 'sigem-badge-neutral';
        }
    }"
    @keydown.window.ctrl.k.prevent="buscaMobile = true; $nextTick(() => { $refs.inputBuscaHeader?.focus(); $refs.inputBuscaHeader?.select() })"
    @keydown.window.meta.k.prevent="buscaMobile = true; $nextTick(() => { $refs.inputBuscaHeader?.focus(); $refs.inputBuscaHeader?.select() })">
    <div class="flex min-w-0 flex-wrap items-center gap-2 px-3 py-2.5 md:gap-3 md:px-6">
        {{-- Recolher menu (desktop) --}}
        <button type="button" class="hidden h-9 w-9 items-center justify-center rounded-md border border-sigem-line text-sigem-muted hover:bg-sigem-neutral-soft xl:flex dark:border-gray-800 dark:text-gray-400 dark:hover:bg-white/5"
            @click="$store.sidebar.toggleExpanded()" aria-label="Recolher ou expandir menu">
            <svg aria-hidden="true" class="h-[18px] w-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        {{-- Abrir menu (celular/tablet) --}}
        <button type="button" data-mobile-menu-toggle
            class="flex h-10 w-10 items-center justify-center rounded-md text-sigem-ink hover:bg-sigem-neutral-soft xl:hidden dark:text-gray-200 dark:hover:bg-white/5"
            aria-label="Abrir menu" :aria-expanded="$store.sidebar ? $store.sidebar.isMobileOpen : false">
            <svg aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <a href="/" class="flex items-center gap-2 no-underline xl:hidden">
            <img src="{{ asset(config('app.icon_path', 'logo/icone.png')) }}" alt="" class="h-7 w-7 rounded object-contain">
            <strong class="text-sm text-sigem-ink dark:text-white">Phoenix SIGEM</strong>
        </a>

        {{-- Busca global --}}
        <div class="relative order-last w-full md:order-none md:w-auto md:max-w-[520px] md:flex-1"
            :class="buscaMobile ? 'block' : 'hidden md:block'" @click.away="aberto = false">
            <form role="search" action="{{ route('busca.global') }}" method="GET" @submit.prevent="submeterBusca()"
                class="flex h-9 items-center gap-2 rounded-md border border-sigem-field bg-white px-2.5 focus-within:border-sigem-primary focus-within:ring-2 focus-within:ring-sigem-primary/20 dark:border-gray-700 dark:bg-gray-900">
                <svg aria-hidden="true" class="h-4 w-4 shrink-0 text-sigem-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg>
                <label for="busca-global" class="sr-only">Buscar no sistema</label>
                <input id="busca-global" name="q" type="search" x-ref="inputBuscaHeader" x-model="termoBusca" @input="onInput()"
                    @focus="if ((termoBusca || '').length >= 2) aberto = true" @keydown.escape="aberto = false"
                    placeholder="Buscar processo, demanda, placa ou cidadão" autocomplete="off"
                    class="h-full min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-sigem-ink placeholder:text-gray-400 focus:outline-none focus:ring-0 dark:text-white">
                <svg x-show="carregando" x-cloak aria-hidden="true" class="h-4 w-4 animate-spin text-sigem-primary" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                <kbd x-show="!carregando" class="hidden shrink-0 rounded border border-sigem-line px-1.5 font-sans text-xs text-sigem-muted md:inline dark:border-gray-700 dark:text-gray-400">Ctrl K</kbd>
            </form>

            <div x-show="aberto" x-cloak
                class="absolute left-0 right-0 top-11 z-[999999] max-h-[460px] overflow-y-auto rounded-lg border border-sigem-line bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
                <template x-if="carregando && resultados.length === 0">
                    <p class="m-0 p-4 text-center text-[13px] text-sigem-muted">Buscando…</p>
                </template>
                <template x-if="!carregando && resultados.length === 0">
                    <p class="m-0 p-4 text-center text-[13px] text-sigem-muted">Nada encontrado para “<span x-text="termoBusca" class="font-semibold text-sigem-ink dark:text-white"></span>”.</p>
                </template>
                <template x-for="section in resultados" :key="section.key">
                    <div class="border-b border-gray-100 p-1.5 last:border-0 dark:border-gray-800">
                        <div class="flex items-center justify-between px-2.5 py-1 text-xs text-sigem-muted">
                            <span x-text="section.title"></span><span x-text="section.items.length"></span>
                        </div>
                        <template x-for="item in section.items" :key="item.id">
                            <a :href="item.url" class="flex items-center justify-between gap-3 rounded-md px-2.5 py-2 no-underline hover:bg-gray-50 dark:hover:bg-white/5">
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-semibold text-sigem-ink dark:text-white" x-text="item.title"></span>
                                    <span class="block truncate text-xs text-sigem-muted" x-text="item.subtitle"></span>
                                </span>
                                <span class="sigem-badge shrink-0" :class="tom(item.badge_type)" x-text="item.badge"></span>
                            </a>
                        </template>
                    </div>
                </template>
                <button type="button" @click="submeterBusca()" class="block w-full border-t border-gray-100 bg-gray-50 px-3 py-2 text-center text-[13px] font-semibold text-sigem-primary hover:underline dark:border-gray-800 dark:bg-white/5 dark:text-brand-300">
                    Ver todos os resultados (Enter)
                </button>
            </div>
        </div>

        <div class="ml-auto flex items-center gap-1.5">
            <button type="button" class="flex h-9 w-9 items-center justify-center rounded-md text-sigem-ink hover:bg-sigem-neutral-soft md:hidden dark:text-gray-200 dark:hover:bg-white/5"
                @click="buscaMobile = !buscaMobile; $nextTick(() => $refs.inputBuscaHeader?.focus())" aria-label="Buscar">
                <svg aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg>
            </button>
            <button type="button" @click="$store.theme.toggle()" aria-label="Alternar tema claro/escuro"
                class="flex h-9 w-9 items-center justify-center rounded-md text-sigem-muted hover:bg-sigem-neutral-soft hover:text-sigem-ink dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white">
                <svg aria-hidden="true" class="hidden h-[18px] w-[18px] dark:block" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
                <svg aria-hidden="true" class="h-[18px] w-[18px] dark:hidden" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>
            </button>
            <x-header.notification-dropdown />
            <x-header.user-dropdown />
        </div>
    </div>
</header>
