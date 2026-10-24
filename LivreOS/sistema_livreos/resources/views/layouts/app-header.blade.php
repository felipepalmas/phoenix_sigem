{{--
 * Componente da aplicação Phoenix - SIGEM
 * Cabeçalho do Sistema com Ferramenta Central de Busca Municipal (Omnisearch)
 *
 * @license AGPL-3.0
 --}}

<header
    class="sticky top-0 flex min-w-0 w-full max-w-full bg-white border-gray-200 z-[99999] dark:border-gray-800 dark:bg-gray-900 xl:border-b"
    x-data="{
        isApplicationMenuOpen: false,
        termoBusca: @js(request('q', '')),
        resultados: [],
        totalResultados: 0,
        carregando: false,
        aberto: false,
        timeout: null,
        toggleApplicationMenu() {
            this.isApplicationMenuOpen = !this.isApplicationMenuOpen;
        },
        onInput() {
            clearTimeout(this.timeout);
            const q = (this.termoBusca || '').trim();
            if (q.length < 2) {
                this.resultados = [];
                this.totalResultados = 0;
                this.aberto = false;
                return;
            }
            this.carregando = true;
            this.aberto = true;
            this.timeout = setTimeout(() => {
                fetch('{{ route('busca.global.api') }}?q=' + encodeURIComponent(q))
                    .then(r => r.json())
                    .then(data => {
                        this.resultados = data.sections || [];
                        this.totalResultados = data.total || 0;
                        this.carregando = false;
                    })
                    .catch(() => {
                        this.carregando = false;
                    });
            }, 220);
        },
        submeterBusca() {
            const q = (this.termoBusca || '').trim();
            if (q) {
                window.location.href = '{{ route('busca.global') }}?q=' + encodeURIComponent(q);
            }
        },
        fechar() {
            this.aberto = false;
        }
    }"
    @keydown.window.ctrl.k.prevent="$refs.inputBuscaHeader?.focus(); $refs.inputBuscaHeader?.select()"
    @keydown.window.cmd.k.prevent="$refs.inputBuscaHeader?.focus(); $refs.inputBuscaHeader?.select()">

    <div class="flex min-w-0 flex-1 flex-col items-center justify-between xl:flex-row xl:px-6">
        <div
            class="flex min-w-0 items-center justify-between w-full gap-2 px-3 py-3 border-b border-gray-200 dark:border-gray-800 sm:gap-4 xl:justify-normal xl:border-b-0 xl:px-0 lg:py-4">

            <!-- Desktop Sidebar Toggle Button (visible on xl and up) -->
            <button
                class="hidden xl:flex items-center justify-center w-10 h-10 text-gray-500 border border-gray-200 rounded-lg dark:border-gray-800 dark:text-gray-400 lg:h-11 lg:w-11"
                :class="{ 'bg-gray-100 dark:bg-white/[0.03]': !$store.sidebar.isExpanded }"
                @click="$store.sidebar.toggleExpanded()" aria-label="Toggle Sidebar">
                <svg x-show="!$store.sidebar.isMobileOpen" width="16" height="12" viewBox="0 0 16 12" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M0.583252 1C0.583252 0.585788 0.919038 0.25 1.33325 0.25H14.6666C15.0808 0.25 15.4166 0.585786 15.4166 1C15.4166 1.41421 15.0808 1.75 14.6666 1.75L1.33325 1.75C0.919038 1.75 0.583252 1.41422 0.583252 1ZM0.583252 11C0.583252 10.5858 0.919038 10.25 1.33325 10.25L14.6666 10.25C15.0808 10.25 15.4166 10.5858 15.4166 11C15.4166 11.4142 15.0808 11.75 14.6666 11.75L1.33325 11.75C0.919038 11.75 0.583252 11.4142 0.583252 11ZM1.33325 5.25C0.919038 5.25 0.583252 5.58579 0.583252 6C0.583252 6.41421 0.919038 6.75 1.33325 6.75L7.99992 6.75C8.41413 6.75 8.74992 6.41421 8.74992 6C8.74992 5.58579 8.41413 5.25 7.99992 5.25L1.33325 5.25Z"
                        fill="currentColor"></path>
                </svg>
                <svg x-show="$store.sidebar.isMobileOpen" class="fill-current" width="24" height="24" viewBox="0 0 24 24"
                    fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M6.21967 7.28131C5.92678 6.98841 5.92678 6.51354 6.21967 6.22065C6.51256 5.92775 6.98744 5.92775 7.28033 6.22065L11.999 10.9393L16.7176 6.22078C17.0105 5.92789 17.4854 5.92788 17.7782 6.22078C18.0711 6.51367 18.0711 6.98855 17.7782 7.28144L13.0597 12L17.7782 16.7186C18.0711 17.0115 18.0711 17.4863 17.7782 17.7792C17.4854 18.0721 17.0105 18.0721 16.7176 17.7792L11.999 13.0607L7.28033 17.7794C6.98744 18.0722 6.51256 18.0722 6.21967 17.7794C5.92678 17.4865 5.92678 17.0116 6.21967 16.7187L10.9384 12L6.21967 7.28131Z"
                        fill="" />
                </svg>
            </button>

            <!-- Mobile Menu Toggle Button (visible below xl) -->
            <button type="button"
                data-mobile-menu-toggle
                class="flex xl:hidden items-center justify-center w-10 h-10 min-h-[44px] min-w-[44px] text-gray-500 rounded-lg dark:text-gray-400 lg:h-11 lg:w-11"
                :class="{ 'bg-gray-100 dark:bg-white/[0.03]': $store.sidebar && $store.sidebar.isMobileOpen }"
                aria-label="Abrir ou fechar menu"
                :aria-expanded="$store.sidebar ? $store.sidebar.isMobileOpen : false">
                <svg x-show="!$store.sidebar.isMobileOpen" width="16" height="12" viewBox="0 0 16 12" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M0.583252 1C0.583252 0.585788 0.919038 0.25 1.33325 0.25H14.6666C15.0808 0.25 15.4166 0.585786 15.4166 1C15.4166 1.41421 15.0808 1.75 14.6666 1.75L1.33325 1.75C0.919038 1.75 0.583252 1.41422 0.583252 1ZM0.583252 11C0.583252 10.5858 0.919038 10.25 1.33325 10.25L14.6666 10.25C15.0808 10.25 15.4166 10.5858 15.4166 11C15.4166 11.4142 15.0808 11.75 14.6666 11.75L1.33325 11.75C0.919038 11.75 0.583252 11.4142 0.583252 11ZM1.33325 5.25C0.919038 5.25 0.583252 5.58579 0.583252 6C0.583252 6.41421 0.919038 6.75 1.33325 6.75L7.99992 6.75C8.41413 6.75 8.74992 6.41421 8.74992 6C8.74992 5.58579 8.41413 5.25 7.99992 5.25L1.33325 5.25Z"
                        fill="currentColor"></path>
                </svg>
                <svg x-show="$store.sidebar.isMobileOpen" class="fill-current" width="24" height="24" viewBox="0 0 24 24"
                    fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M6.21967 7.28131C5.92678 6.98841 5.92678 6.51354 6.21967 6.22065C6.51256 5.92775 6.98744 5.92775 7.28033 6.22065L11.999 10.9393L16.7176 6.22078C17.0105 5.92789 17.4854 5.92788 17.7782 6.22078C18.0711 6.51367 18.0711 6.98855 17.7782 7.28144L13.0597 12L17.7782 16.7186C18.0711 17.0115 18.0711 17.4863 17.7782 17.7792C17.4854 18.0721 17.0105 18.0721 16.7176 17.7792L11.999 13.0607L7.28033 17.7794C6.98744 18.0722 6.51256 18.0722 6.21967 17.7794C5.92678 17.4865 5.92678 17.0116 6.21967 16.7187L10.9384 12L6.21967 7.28131Z"
                        fill="" />
                </svg>
            </button>

            @php
                $headerEmpresa = \App\Models\Empresa::first();
                $headerEmpresaNome = $headerEmpresa?->nome ?: $headerEmpresa?->razao_social ?: config('app.name');
                $headerEmpresaLogoUrl = null;
                if ($headerEmpresa?->logo_path) {
                    $headerLogoPath = storage_path('app/public/' . $headerEmpresa->logo_path);
                    if (file_exists($headerLogoPath)) {
                        $headerMime = @mime_content_type($headerLogoPath) ?: 'image/png';
                        $headerEmpresaLogoUrl = 'data:' . $headerMime . ';base64,' . base64_encode(file_get_contents($headerLogoPath));
                    }
                }
            @endphp
            <!-- Logo (mobile only) -->
            <a href="/" class="xl:hidden flex items-center gap-2 min-w-0 max-w-[140px] rounded-lg p-1 -m-1 dark:bg-white/5">
                @if($headerEmpresaLogoUrl)
                    <img src="{{ $headerEmpresaLogoUrl }}" alt="{{ $headerEmpresaNome }}" class="max-h-8 w-auto object-contain dark:max-h-8" />
                @else
                    <img src="{{ asset(config('app.icon_path', 'logo/icone.png')) }}" alt="{{ $headerEmpresaNome }}" class="h-7 w-7 object-contain" />
                    <span class="truncate text-sm font-bold text-gray-800 dark:text-white/90">Phoenix <span class="text-amber-500">SIGEM</span></span>
                @endif
            </a>

            <!-- Application Menu Toggle (mobile only) -->
            <button @click="toggleApplicationMenu()"
                class="flex items-center justify-center w-10 h-10 text-gray-700 rounded-lg z-[99999] hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 xl:hidden">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M5.99902 10.4951C6.82745 10.4951 7.49902 11.1667 7.49902 11.9951V12.0051C7.49902 12.8335 6.82745 13.5051 5.99902 13.5051C5.1706 13.5051 4.49902 12.8335 4.49902 12.0051V11.9951C4.49902 11.1667 5.1706 10.4951 5.99902 10.4951ZM17.999 10.4951C18.8275 10.4951 19.499 11.1667 19.499 11.9951V12.0051C19.499 12.8335 18.8275 13.5051 17.999 13.5051C17.1706 13.5051 16.499 12.8335 16.499 12.0051V11.9951C16.499 11.1667 17.1706 10.4951 17.999 10.4951ZM13.499 11.9951C13.499 11.1667 12.8275 10.4951 11.999 10.4951C11.1706 10.4951 10.499 11.1667 10.499 11.9951V12.0051C10.499 12.8335 11.1706 13.5051 11.999 13.5051C12.8275 13.5051 13.499 12.8335 13.499 12.0051V11.9951Z"
                        fill="currentColor" />
                </svg>
            </button>

            <!-- FERRAMENTA DE BUSCA GLOBAL MUNICIPAL (DESKTOP) -->
            <div class="hidden xl:flex xl:min-w-[360px] xl:max-w-[480px] relative" @click.away="fechar()">
                <div class="relative flex w-full items-center">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" x-ref="inputBuscaHeader" x-model="termoBusca" @input="onInput()" @focus="if((termoBusca || '').length >= 2) aberto = true" @keydown.enter.prevent="submeterBusca()" @keydown.escape="fechar()"
                        placeholder="Buscar no sistema (Processos, Zeladoria, Cidadãos...)"
                        autocomplete="off"
                        class="h-10 w-full min-w-0 rounded-l-xl border border-r-0 border-gray-200 bg-gray-50/80 pl-9 pr-14 py-2 text-xs font-medium text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-500">
                    
                    <!-- Indicador de Atalho Ctrl+K ou Spinner de Carregamento -->
                    <div class="absolute right-16 flex items-center pr-1 pointer-events-none">
                        <template x-if="carregando">
                            <svg class="animate-spin h-3.5 w-3.5 text-brand-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        </template>
                        <template x-if="!carregando">
                            <span class="rounded bg-gray-200/80 px-1 py-0.5 text-[9px] font-mono text-gray-500 dark:bg-gray-700 dark:text-gray-400">Ctrl K</span>
                        </template>
                    </div>

                    <button type="button" @click="submeterBusca()" class="h-10 shrink-0 rounded-r-xl bg-brand-600 px-3.5 py-2 text-xs font-bold text-white hover:bg-brand-700 dark:bg-brand-600 dark:hover:bg-brand-500 transition">
                        Buscar
                    </button>
                </div>

                <!-- Dropdown Flutuante de Busca Rápida (Instant Search) -->
                <div x-show="aberto" x-cloak
                    class="absolute left-0 right-0 top-12 z-[999999] max-h-[460px] overflow-y-auto rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-700 dark:bg-gray-800">
                    
                    <template x-if="carregando && resultados.length === 0">
                        <div class="p-4 text-center text-xs text-gray-400">
                            <svg class="inline-block animate-spin h-4 w-4 mr-1.5 text-brand-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            Consultando registros autorizados...
                        </div>
                    </template>

                    <template x-if="!carregando && resultados.length === 0">
                        <div class="p-5 text-center text-xs text-gray-400">
                            Nenhum resultado encontrado para "<span x-text="termoBusca" class="font-bold text-gray-700 dark:text-gray-300"></span>".
                        </div>
                    </template>

                    <!-- Seções Categorizadas por Módulo -->
                    <template x-for="section in resultados" :key="section.key">
                        <div class="border-b border-gray-100 last:border-0 dark:border-gray-700/60 p-2">
                            <div class="px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider text-gray-400 dark:text-gray-400 flex items-center justify-between">
                                <span x-text="section.title"></span>
                                <span class="rounded-full bg-gray-100 dark:bg-gray-700 px-1.5 py-0.2 text-[9px] text-gray-500 dark:text-gray-300" x-text="section.items.length"></span>
                            </div>
                            <div class="mt-1 space-y-1">
                                <template x-for="item in section.items" :key="item.id">
                                    <a :href="item.url" class="flex items-center justify-between rounded-xl px-2.5 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-700/50 transition group">
                                        <div class="min-w-0 pr-2">
                                            <div class="font-bold text-gray-900 group-hover:text-brand-600 dark:text-white dark:group-hover:text-brand-400 truncate" x-text="item.title"></div>
                                            <div class="text-[11px] text-gray-500 dark:text-gray-400 truncate" x-text="item.subtitle"></div>
                                        </div>
                                        <span class="shrink-0 rounded px-1.5 py-0.5 text-[9px] font-bold"
                                            :class="{
                                                'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300': item.badge_type === 'info',
                                                'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300': item.badge_type === 'danger',
                                                'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300': item.badge_type === 'warning',
                                                'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300': item.badge_type === 'success'
                                            }"
                                            x-text="item.badge">
                                        </span>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Rodapé do Dropdown de Busca -->
                    <div class="bg-gray-50 p-2.5 text-center text-xs dark:bg-gray-900/60 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" @click="submeterBusca()" class="w-full text-center font-bold text-brand-600 hover:underline dark:text-brand-400">
                            Pressione Enter para ver todos os resultados no Painel Completo →
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Application Menu (mobile) and Right Side Actions (desktop) -->
        <div :class="isApplicationMenuOpen ? 'flex' : 'hidden'"
            class="items-center justify-between w-full gap-4 px-5 py-4 xl:flex shadow-theme-md xl:justify-end xl:px-0 xl:shadow-none">
            
            <!-- Campo de Busca no Menu Mobile -->
            <div class="w-full xl:hidden mb-2">
                <form action="{{ route('busca.global') }}" method="GET" class="flex items-center gap-1.5 w-full">
                    <input type="text" name="q" value="{{ request('q', '') }}" placeholder="Buscar Processos, Zeladoria..."
                        class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                    <button type="submit" class="h-9 rounded-lg bg-brand-600 px-3 text-xs font-bold text-white">
                        Buscar
                    </button>
                </form>
            </div>

            <div class="flex items-center gap-2 2xsm:gap-3">
                <!-- Theme Toggle Button -->
                <button
                    class="relative flex items-center justify-center h-11 w-11 rounded-full border border-gray-200 bg-white text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white"
                    @click="$store.theme.toggle()">
                    <svg class="hidden dark:block" width="20" height="20" viewBox="0 0 20 20" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M9.99998 1.5415C10.4142 1.5415 10.75 1.87729 10.75 2.2915V3.5415C10.75 3.95572 10.4142 4.2915 9.99998 4.2915C9.58577 4.2915 9.24998 3.95572 9.24998 3.5415V2.2915C9.24998 1.87729 9.58577 1.5415 9.99998 1.5415ZM10.0009 6.79327C8.22978 6.79327 6.79402 8.22904 6.79402 10.0001C6.79402 11.7712 8.22978 13.207 10.0009 13.207C11.772 13.207 13.2078 11.7712 13.2078 10.0001C13.2078 8.22904 11.772 6.79327 10.0009 6.79327ZM5.29402 10.0001C5.29402 7.40061 7.40135 5.29327 10.0009 5.29327C12.6004 5.29327 14.7078 7.40061 14.7078 10.0001C14.7078 12.5997 12.6004 14.707 10.0009 14.707C7.40135 14.707 5.29402 12.5997 5.29402 10.0001ZM15.9813 5.08035C16.2742 4.78746 16.2742 4.31258 15.9813 4.01969C15.6884 3.7268 15.2135 3.7268 14.9207 4.01969L14.0368 4.90357C13.7439 5.19647 13.7439 5.67134 14.0368 5.96423C14.3297 6.25713 14.8045 6.25713 15.0974 5.96423L15.9813 5.08035ZM18.4577 10.0001C18.4577 10.4143 18.1219 10.7501 17.7077 10.7501H16.4577C16.0435 10.7501 15.7077 10.4143 15.7077 10.0001C15.7077 9.58592 16.0435 9.25013 16.4577 9.25013H17.7077C18.1219 9.25013 18.4577 9.58592 18.4577 10.0001ZM14.9207 15.9806C15.2135 16.2735 15.6884 16.2735 15.9813 15.9806C16.2742 15.6877 16.2742 15.2128 15.9813 14.9199L15.0974 14.036C14.8045 13.7431 14.3297 13.7431 14.0368 14.036C13.7439 14.3289 13.7439 14.8038 14.0368 15.0967L14.9207 15.9806ZM9.99998 15.7088C10.4142 15.7088 10.75 16.0445 10.75 16.4588V17.7088C10.75 18.123 10.4142 18.4588 9.99998 18.4588C9.58577 18.4588 9.24998 18.123 9.24998 17.7088V16.4588C9.24998 16.0445 9.58577 15.7088 9.99998 15.7088ZM5.96356 15.0972C6.25646 14.8043 6.25646 14.3295 5.96356 14.0366C5.67067 13.7437 5.1958 13.7437 4.9029 14.0366L4.01902 14.9204C3.72613 15.2133 3.72613 15.6882 4.01902 15.9811C4.31191 16.274 4.78679 16.274 5.07968 15.9811L5.96356 15.0972ZM4.29224 10.0001C4.29224 10.4143 3.95645 10.7501 3.54224 10.7501H2.29224C1.87802 10.7501 1.54224 10.4143 1.54224 10.0001C1.54224 9.58592 1.87802 9.25013 2.29224 9.25013H3.54224C3.95645 9.25013 4.29224 9.58592 4.29224 10.0001ZM4.9029 5.9637C5.1958 6.25659 5.67067 6.25659 5.96356 5.9637C6.25646 5.6708 6.25646 5.19593 5.96356 4.90303L5.07968 4.01915C4.78679 3.72626 4.31191 3.72626 4.01902 4.01915C3.72613 4.31204 3.72613 4.78692 4.01902 5.07981L4.9029 5.9637Z"
                            fill="currentColor" />
                    </svg>
                    <svg class="dark:hidden" width="20" height="20" viewBox="0 0 20 20" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M17.4547 11.97L18.1799 12.1611C18.265 11.8383 18.1265 11.4982 17.8401 11.3266C17.5538 11.1551 17.1885 11.1934 16.944 11.4207L17.4547 11.97ZM8.0306 2.5459L8.57989 3.05657C8.80718 2.81209 8.84554 2.44682 8.67398 2.16046C8.50243 1.8741 8.16227 1.73559 7.83948 1.82066L8.0306 2.5459ZM12.9154 13.0035C9.64678 13.0035 6.99707 10.3538 6.99707 7.08524H5.49707C5.49707 11.1823 8.81835 14.5035 12.9154 14.5035V13.0035ZM16.944 11.4207C15.8869 12.4035 14.4721 13.0035 12.9154 13.0035V14.5035C14.8657 14.5035 16.6418 13.7499 17.9654 12.5193L16.944 11.4207ZM16.7295 11.7789C15.9437 14.7607 13.2277 16.9586 10.0003 16.9586V18.4586C13.9257 18.4586 17.2249 15.7853 18.1799 12.1611L16.7295 11.7789ZM10.0003 16.9586C6.15734 16.9586 3.04199 13.8433 3.04199 10.0003H1.54199C1.54199 14.6717 5.32892 18.4586 10.0003 18.4586V16.9586ZM3.04199 10.0003C3.04199 6.77289 5.23988 4.05695 8.22173 3.27114L7.83948 1.82066C4.21532 2.77574 1.54199 6.07486 1.54199 10.0003H3.04199ZM6.99707 7.08524C6.99707 5.52854 7.5971 4.11366 8.57989 3.05657L7.48132 2.03522C6.25073 3.35885 5.49707 5.13487 5.49707 7.08524H6.99707Z"
                            fill="currentColor" />
                    </svg>
                </button>

                <!-- Notification Dropdown -->
                <x-header.notification-dropdown />
            </div>

            <!-- User Dropdown -->
            <x-header.user-dropdown />
        </div>
    </div>
</header>
