{{--
 * Componente da aplicação Phoenix - SIGEM
 * Layout do Portal do Cidadão (padrão SIGEM Clean, linguagem simples)
 *
 * @license AGPL-3.0
 --}}
@php
    $orgao = \App\Models\Empresa::first();
    $orgaoNome = $orgao?->nome ?: ($orgao?->razao_social ?: 'Prefeitura Municipal');
    $orgaoLogo = null;
    if ($orgao?->logo_path && file_exists(storage_path('app/public/' . $orgao->logo_path))) {
        $orgaoLogo = asset('storage/' . $orgao->logo_path);
    }
@endphp
<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Serviços ao cidadão' }} · {{ $orgaoNome }}</title>
    <link rel="icon" type="image/png" href="{{ asset('logo/icone.png') }}">
    @include('partials.vite-assets')
    <script>
        (function () {
            try {
                var t = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                if (t === 'dark') document.documentElement.classList.add('dark');
            } catch (e) {}
        })();
        document.addEventListener('alpine:init', function () {
            Alpine.store('theme', {
                theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
                toggle() {
                    this.theme = this.theme === 'dark' ? 'light' : 'dark';
                    try { localStorage.setItem('theme', this.theme); } catch (e) {}
                    document.documentElement.classList.toggle('dark', this.theme === 'dark');
                }
            });
        });
    </script>
</head>
<body class="flex min-h-full flex-col bg-sigem-canvas text-sigem-ink antialiased dark:bg-gray-950 dark:text-gray-100">
    <a href="#conteudo" class="sr-only focus:not-sr-only focus:fixed focus:left-3 focus:top-3 focus:z-50 focus:rounded-md focus:bg-white focus:px-3 focus:py-2 focus:shadow">Pular para o conteúdo</a>

    <header class="border-b border-sigem-line bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center gap-3 px-4 py-3">
            <a href="{{ route('portal.index') }}" class="flex min-w-0 items-center gap-2.5 no-underline">
                <img src="{{ $orgaoLogo ?: asset('logo/icone.png') }}" alt="" class="h-9 w-9 shrink-0 rounded object-contain">
                <span class="flex min-w-0 flex-col leading-tight">
                    <strong class="truncate text-[15px] text-sigem-ink dark:text-white">{{ $orgaoNome }}</strong>
                    <span class="text-xs text-sigem-muted dark:text-gray-400">Serviços ao cidadão</span>
                </span>
            </a>
            <nav aria-label="Serviços" class="order-last flex w-full gap-1 overflow-x-auto text-sm md:order-none md:ml-6 md:w-auto">
                @foreach([['Início', 'portal.index', ['portal.index']], ['Pedir um serviço', 'portal.zeladoria.nova', ['portal.zeladoria.*']], ['Consultar processo', 'portal.index', ['portal.processos']], ['Validar documento', 'portal.autenticidade', ['portal.autenticidade']]] as [$rotulo, $rota, $ativas])
                    @php $ativo = request()->routeIs(...$ativas) && !($rotulo === 'Início' && request()->routeIs('portal.processos')); @endphp
                    <a href="{{ route($rota) }}{{ $rotulo === 'Consultar processo' ? '#consultar' : '' }}" @if($ativo) aria-current="page" @endif
                        class="whitespace-nowrap rounded-md px-3 py-2 no-underline {{ $ativo ? 'bg-sigem-primary-soft font-semibold text-sigem-primary dark:bg-brand-500/15 dark:text-brand-300' : 'text-sigem-ink hover:bg-sigem-neutral-soft dark:text-gray-200 dark:hover:bg-white/5' }}">{{ $rotulo }}</a>
                @endforeach
            </nav>
            <div class="ml-auto flex items-center gap-1.5">
                <button type="button" @click="$store.theme.toggle()" aria-label="Alternar tema claro/escuro"
                    class="flex h-9 w-9 items-center justify-center rounded-md text-sigem-muted hover:bg-sigem-neutral-soft dark:text-gray-400 dark:hover:bg-white/5">
                    <svg aria-hidden="true" class="h-[18px] w-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>
                </button>
                @auth
                    <a href="{{ route('dashboard') }}" class="sigem-btn sigem-btn-secondary sigem-btn-sm">Área do servidor</a>
                @else
                    <a href="{{ route('signin') }}" class="sigem-btn sigem-btn-ghost sigem-btn-sm">Sou servidor</a>
                @endauth
            </div>
        </div>
    </header>

    <main id="conteudo" class="flex-1 py-8">
        <div class="mx-auto max-w-6xl px-4">
            @yield('content')
        </div>
    </main>

    <footer class="border-t border-sigem-line bg-white text-[13px] text-sigem-muted dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400">
        <div class="mx-auto grid max-w-6xl gap-6 px-4 py-8 md:grid-cols-3">
            <div>
                <strong class="text-sigem-ink dark:text-white">{{ $orgaoNome }}</strong>
                @if($orgao?->logradouro)
                    <p class="m-0 mt-1">{{ $orgao->logradouro }}{{ $orgao->numero ? ', ' . $orgao->numero : '' }}{{ $orgao->bairro ? ' — ' . $orgao->bairro : '' }}{{ $orgao->cidade ? ', ' . $orgao->cidade : '' }}{{ $orgao->estado ? '/' . $orgao->estado : '' }}</p>
                @endif
                @if($orgao?->telefone)<p class="m-0 mt-1">Telefone: {{ $orgao->telefone }}</p>@endif
                @if($orgao?->email)<p class="m-0 mt-1">E-mail: <a href="mailto:{{ $orgao->email }}" class="text-sigem-muted underline dark:text-gray-400">{{ $orgao->email }}</a></p>@endif
            </div>
            <div>
                <strong class="text-sigem-ink dark:text-white">Seus dados</strong>
                <p class="m-0 mt-1">Nesta página o seu nome e CPF aparecem mascarados (LGPD, Lei 13.709/2018). Processos sigilosos não são exibidos (LAI, Lei 12.527/2011).</p>
            </div>
            <div>
                <strong class="text-sigem-ink dark:text-white">Sobre o sistema</strong>
                <p class="m-0 mt-1">Phoenix SIGEM, software livre sob licença <a href="https://github.com/felipepalmas/phoenix_sigem" target="_blank" rel="noopener" class="text-sigem-muted underline dark:text-gray-400">AGPL-3.0 (código-fonte)</a>.</p>
            </div>
        </div>
    </footer>
    @stack('scripts')
</body>
</html>
