

<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e($title ?? 'Portal de Serviços ao Cidadão'); ?> | Phoenix - SIGEM</title>

    <link rel="icon" type="image/png" href="<?php echo e(asset('icone.png')); ?>">

    <!-- Estilos & Scripts Vite -->
    <?php echo $__env->make('partials.vite-assets', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- Inicialização Theme Store Dark Mode -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                init() {
                    const savedTheme = localStorage.getItem('theme');
                    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                    this.theme = savedTheme || systemTheme;
                    this.updateTheme();
                },
                theme: 'light',
                toggle() {
                    this.theme = this.theme === 'light' ? 'dark' : 'light';
                    localStorage.setItem('theme', this.theme);
                    this.updateTheme();
                },
                updateTheme() {
                    const html = document.documentElement;
                    const body = document.body;
                    if (this.theme === 'dark') {
                        html.classList.add('dark');
                        if (body) body.classList.add('dark', 'bg-gray-900');
                    } else {
                        html.classList.remove('dark');
                        if (body) body.classList.remove('dark', 'bg-gray-900');
                    }
                }
            });
        });
    </script>

    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            const theme = savedTheme || systemTheme;
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>
</head>

<body class="min-h-full flex flex-col bg-gray-50 text-gray-900 dark:bg-gray-900 dark:text-gray-100 antialiased font-sans">

    <!-- Topbar Oficial do Município -->
    <header class="sticky top-0 z-40 border-b border-gray-200 bg-white/95 backdrop-blur-md dark:border-gray-800 dark:bg-gray-900/95 transition-colors">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between gap-4">

                <!-- Logo & Nome Oficial da Prefeitura -->
                <a href="<?php echo e(route('portal.index')); ?>" class="flex items-center gap-3 group">
                    <img src="<?php echo e(asset('phoenix_logo.png')); ?>" alt="Logo Phoenix SIGEM" class="h-10 w-auto object-contain transition group-hover:scale-105">
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-base font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-lg">
                                Phoenix <span class="text-brand-600 dark:text-brand-400">SIGEM</span>
                            </span>
                            <span class="rounded bg-blue-100 px-1.5 py-0.5 text-[10px] font-extrabold text-blue-800 dark:bg-blue-900/50 dark:text-blue-300">
                                CIDADÃO
                            </span>
                        </div>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 -mt-0.5 font-medium">
                            Prefeitura Municipal • Serviços Digitais
                        </p>
                    </div>
                </a>

                <!-- Navegação Pública -->
                <nav class="hidden md:flex items-center gap-1 text-xs font-bold">
                    <a href="<?php echo e(route('portal.index')); ?>" class="rounded-xl px-3.5 py-2 text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800 transition <?php echo e(request()->routeIs('portal.index') ? 'bg-gray-100 dark:bg-gray-800 text-brand-600 dark:text-brand-400' : ''); ?>">
                        Início
                    </a>
                    <a href="<?php echo e(route('portal.zeladoria.nova')); ?>" class="rounded-xl px-3.5 py-2 text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800 transition <?php echo e(request()->routeIs('portal.zeladoria.*') ? 'bg-gray-100 dark:bg-gray-800 text-brand-600 dark:text-brand-400' : ''); ?>">
                        Ouvidoria & Zeladoria
                    </a>
                    <a href="<?php echo e(route('portal.autenticidade')); ?>" class="rounded-xl px-3.5 py-2 text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800 transition <?php echo e(request()->routeIs('portal.autenticidade') ? 'bg-gray-100 dark:bg-gray-800 text-brand-600 dark:text-brand-400' : ''); ?>">
                        Validar Documento
                    </a>
                </nav>

                <!-- Ações da Direita (Tema & Acesso Servidor) -->
                <div class="flex items-center gap-2.5">
                    <!-- Botão Toggle Dark Mode -->
                    <button type="button" @click="$store.theme.toggle()" title="Alternar tema claro/escuro"
                        class="flex h-9 w-9 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-600 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition">
                        <template x-if="$store.theme.theme === 'dark'">
                            <svg class="h-4 w-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </template>
                        <template x-if="$store.theme.theme === 'light'">
                            <svg class="h-4 w-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        </template>
                    </button>

                    <?php if(auth()->guard()->check()): ?>
                        <a href="<?php echo e(route('dashboard')); ?>" class="inline-flex items-center gap-1.5 rounded-xl bg-brand-600 px-3.5 py-2 text-xs font-bold text-white shadow-sm hover:bg-brand-700 transition">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            Painel Interno
                        </a>
                    <?php else: ?>
                        <a href="<?php echo e(route('signin')); ?>" class="inline-flex items-center gap-1.5 rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition">
                            <svg class="h-3.5 w-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                            Acesso Servidor
                        </a>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </header>

    <!-- Conteúdo Dinâmico -->
    <main class="flex-1 py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </main>

    <!-- Rodapé Institucional com Diretrizes Legais -->
    <footer class="border-t border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900/90 text-xs text-gray-500 dark:text-gray-400 mt-12 transition-colors">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                <div class="md:col-span-2">
                    <div class="flex items-center gap-2 mb-2">
                        <img src="<?php echo e(asset('icone.png')); ?>" alt="Ícone Phoenix" class="h-6 w-6 object-contain">
                        <span class="font-bold text-gray-900 dark:text-white">Phoenix - SIGEM</span>
                    </div>
                    <p class="text-xs leading-relaxed max-w-md text-gray-600 dark:text-gray-400">
                        Sistema Integrado de Gestão Municipal e Governo Digital. Desenvolvido para proporcionar transparência, agilidade sem papel e eficiência na prestação de serviços públicos ao munícipe.
                    </p>
                </div>

                <div>
                    <h4 class="font-bold uppercase tracking-wider text-gray-900 dark:text-white mb-2 text-[11px]">
                        Conformidade Legal
                    </h4>
                    <ul class="space-y-1.5 text-xs text-gray-600 dark:text-gray-400">
                        <li>• Lei 14.129/2021 (Governo Digital)</li>
                        <li>• Lei 12.527/2011 (Acesso à Informação)</li>
                        <li>• Lei 13.709/2018 (LGPD - Proteção de Dados)</li>
                        <li>• Lei 14.063/2020 (Assinatura Eletrônica)</li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold uppercase tracking-wider text-gray-900 dark:text-white mb-2 text-[11px]">
                        Atendimento Municipal
                    </h4>
                    <ul class="space-y-1.5 text-xs text-gray-600 dark:text-gray-400">
                        <li>Segunda a Sexta: 08:00 às 17:00</li>
                        <li>Ouvidoria: 156 / Atendimento Digital</li>
                        <li>E-mail: transparencia@municipio.gov.br</li>
                        <li>Sede da Prefeitura Municipal</li>
                    </ul>
                </div>
            </div>

            <div class="mt-8 border-t border-gray-100 pt-6 dark:border-gray-800 flex flex-col sm:flex-row items-center justify-between text-[11px] text-gray-400">
                <p>© <?php echo e(date('Y')); ?> Prefeitura Municipal — Todos os direitos reservados. Software Livre sob licença AGPL-3.0.</p>
                <p class="mt-2 sm:mt-0">Powered by <strong>Phoenix - SIGEM</strong></p>
            </div>
        </div>
    </footer>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\Users\Felipe\Desktop\LivreOS-main\LivreOS\sistema_livreos\resources\views/layouts/portal.blade.php ENDPATH**/ ?>