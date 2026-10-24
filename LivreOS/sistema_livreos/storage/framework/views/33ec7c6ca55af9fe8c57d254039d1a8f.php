<?php $__env->startSection('content'); ?>
<div class="space-y-6 max-w-4xl mx-auto">

    <!-- Mensagens de Flash -->
    <?php if(session('success')): ?>
        <div class="rounded-3xl border border-emerald-300 bg-emerald-50 p-5 dark:border-emerald-700/60 dark:bg-emerald-900/20 shadow-theme-xs">
            <div class="flex items-center gap-3">
                <div class="rounded-2xl bg-emerald-100 p-2.5 text-emerald-800 dark:bg-emerald-800/40 dark:text-emerald-300">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-emerald-900 dark:text-emerald-200">Protocolo Gerado com Sucesso!</h3>
                    <p class="text-xs text-emerald-800 dark:text-emerald-300"><?php echo e(session('success')); ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
        <a href="<?php echo e(route('portal.index')); ?>" class="hover:text-brand-600 transition">Portal do Cidadão</a>
        <span>/</span>
        <a href="<?php echo e(route('portal.zeladoria.nova')); ?>" class="hover:text-brand-600 transition">Ouvidoria</a>
        <span>/</span>
        <span class="text-gray-900 dark:text-white font-semibold">Protocolo <?php echo e($codigo); ?></span>
    </nav>

    <?php if(isset($erro) && $erro): ?>
        <!-- Demanda Não Encontrada -->
        <div class="rounded-3xl border border-red-200 bg-white p-12 text-center shadow-theme-sm dark:border-red-900/40 dark:bg-gray-800">
            <h2 class="text-xl font-extrabold text-gray-900 dark:text-white">Demanda Não Localizada</h2>
            <p class="mt-2 text-xs text-gray-600 dark:text-gray-400"><?php echo e($erro); ?></p>
            <div class="mt-6">
                <a href="<?php echo e(route('portal.index')); ?>" class="rounded-xl bg-brand-600 px-5 py-2.5 text-xs font-bold text-white hover:bg-brand-700">
                    Voltar ao Início
                </a>
            </div>
        </div>
    <?php else: ?>
        <!-- Cartão Principal do Protocolo -->
        <div class="rounded-3xl border border-gray-200 bg-white p-6 sm:p-8 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800">

            <div class="flex flex-col gap-4 border-b border-gray-100 pb-6 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="rounded-xl bg-brand-100 px-3 py-1 font-mono text-base font-black text-brand-900 dark:bg-brand-900/50 dark:text-brand-300">
                            <?php echo e($demanda->codigo_interno); ?>

                        </span>
                        <?php echo $demanda->status_badge; ?>

                    </div>
                    <h1 class="text-2xl font-black text-gray-900 dark:text-white">
                        <?php echo e($demanda->categoriaZeladoria?->nome ?? 'Demanda de Zeladoria'); ?>

                    </h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Aberto em: <strong><?php echo e($demanda->data_abertura ? $demanda->data_abertura->format('d/m/Y \à\s H:i') : $demanda->created_at->format('d/m/Y \à\s H:i')); ?></strong>
                        <?php if($demanda->secretariaResponsavel): ?>
                            • Secretaria Responsável: <strong><?php echo e($demanda->secretariaResponsavel->sigla); ?> - <?php echo e($demanda->secretariaResponsavel->nome); ?></strong>
                        <?php endif; ?>
                    </p>
                </div>

                <div class="flex items-center gap-2.5">
                    <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-xs font-bold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Imprimir Comprovante
                    </button>
                    <a href="<?php echo e(route('portal.index')); ?>" class="rounded-xl bg-gray-100 px-4 py-2.5 text-xs font-bold text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">
                        Início
                    </a>
                </div>
            </div>

            <!-- Esteira de Progresso Visual (Status Tracker) -->
            <div class="py-8 border-b border-gray-100 dark:border-gray-700">
                <div class="relative">
                    <div class="absolute left-0 top-1/2 -mt-0.5 h-1 w-full bg-gray-200 dark:bg-gray-700"></div>
                    <?php
                        $etapa = match($demanda->status) {
                            'concluida' => 4,
                            'em_andamento' => 3,
                            'aberto' => 2,
                            default => 1,
                        };
                    ?>
                    <div class="relative flex justify-between">
                        <!-- Etapa 1 -->
                        <div class="flex flex-col items-center">
                            <div class="flex h-9 w-9 items-center justify-center rounded-full font-bold text-xs <?php echo e($etapa >= 1 ? 'bg-brand-600 text-white' : 'bg-gray-200 text-gray-500'); ?>">
                                ✓
                            </div>
                            <span class="mt-2 text-center text-[11px] font-bold text-gray-900 dark:text-white">Registrada</span>
                        </div>

                        <!-- Etapa 2 -->
                        <div class="flex flex-col items-center">
                            <div class="flex h-9 w-9 items-center justify-center rounded-full font-bold text-xs <?php echo e($etapa >= 2 ? 'bg-brand-600 text-white' : 'bg-gray-200 text-gray-500'); ?>">
                                <?php echo e($etapa >= 2 ? '✓' : '2'); ?>

                            </div>
                            <span class="mt-2 text-center text-[11px] font-bold text-gray-900 dark:text-white">Triagem Técnica</span>
                        </div>

                        <!-- Etapa 3 -->
                        <div class="flex flex-col items-center">
                            <div class="flex h-9 w-9 items-center justify-center rounded-full font-bold text-xs <?php echo e($etapa >= 3 ? 'bg-blue-600 text-white animate-pulse' : 'bg-gray-200 text-gray-500'); ?>">
                                <?php echo e($etapa >= 3 ? '✓' : '3'); ?>

                            </div>
                            <span class="mt-2 text-center text-[11px] font-bold text-gray-900 dark:text-white">Equipe em Campo</span>
                        </div>

                        <!-- Etapa 4 -->
                        <div class="flex flex-col items-center">
                            <div class="flex h-9 w-9 items-center justify-center rounded-full font-bold text-xs <?php echo e($etapa >= 4 ? 'bg-emerald-600 text-white' : 'bg-gray-200 text-gray-500'); ?>">
                                <?php echo e($etapa >= 4 ? '★' : '4'); ?>

                            </div>
                            <span class="mt-2 text-center text-[11px] font-bold text-gray-900 dark:text-white">Concluído</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dados do Munícipe & Endereço -->
            <div class="grid grid-cols-1 gap-6 pt-6 sm:grid-cols-2">
                <div class="rounded-2xl bg-gray-50 p-4 dark:bg-gray-700/30">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400 block mb-2">Local da Demanda</span>
                    <p class="text-xs font-bold text-gray-900 dark:text-white">
                        <?php echo e($demanda->logradouro); ?>, <?php echo e($demanda->numero_logradouro ?? ($demanda->numero ?? 'S/N')); ?>

                    </p>
                    <p class="text-xs text-gray-600 dark:text-gray-300 mt-0.5">
                        Bairro: <strong><?php echo e($demanda->bairro); ?></strong>
                    </p>
                    <?php if($demanda->referencia_local ?? $demanda->complemento): ?>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                            Ref: <?php echo e($demanda->referencia_local ?? $demanda->complemento); ?>

                        </p>
                    <?php endif; ?>
                </div>

                <div class="rounded-2xl bg-gray-50 p-4 dark:bg-gray-700/30">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400 block mb-2">Solicitante (Proteção LGPD)</span>
                    <p class="text-xs font-bold text-gray-900 dark:text-white font-mono">
                        <?php echo e($dadosRequerente['nome']); ?>

                    </p>
                    <p class="text-xs text-gray-600 dark:text-gray-300 mt-0.5 font-mono">
                        CPF: <?php echo e($dadosRequerente['cpf']); ?>

                    </p>
                    <p class="text-[11px] text-emerald-600 dark:text-emerald-400 mt-1 font-semibold">
                        ✓ Protocolo oficial autenticado
                    </p>
                </div>
            </div>

            <!-- Relato do Munícipe -->
            <div class="mt-6">
                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400 block mb-1.5">Relato Registrado</span>
                <div class="rounded-2xl border border-gray-100 bg-white p-4 text-xs text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                    <?php echo e($demanda->relato_cliente); ?>

                </div>
            </div>

            <?php
                $fotoAntes = $demanda->foto_antes ?? (is_array($demanda->fotos_antes) ? ($demanda->fotos_antes[0] ?? null) : null);
                $fotoDepois = $demanda->foto_depois ?? (is_array($demanda->fotos_depois) ? ($demanda->fotos_depois[0] ?? null) : null);
            ?>

            <!-- Seção de Atesto e Fotos Antes / Depois -->
            <?php if(!empty($fotoAntes) || !empty($fotoDepois)): ?>
                <div class="mt-8 border-t border-gray-100 pt-6 dark:border-gray-700">
                    <h3 class="text-base font-extrabold text-gray-900 dark:text-white mb-4">
                        Registro Fotográfico da Ocorrência
                    </h3>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <?php if(!empty($fotoAntes)): ?>
                            <div class="rounded-2xl border border-gray-200 p-4 dark:border-gray-700">
                                <span class="rounded bg-amber-100 px-2 py-0.5 text-[10px] font-black uppercase text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                    FOTO DA SOLICITAÇÃO (ANTES)
                                </span>
                                <div class="mt-3 aspect-video overflow-hidden rounded-xl bg-gray-100 dark:bg-gray-700">
                                    <img src="<?php echo e(asset('storage/' . $fotoAntes)); ?>" alt="Foto antes" class="h-full w-full object-cover">
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if(!empty($fotoDepois)): ?>
                            <div class="rounded-2xl border border-gray-200 p-4 dark:border-gray-700">
                                <span class="rounded bg-emerald-100 px-2 py-0.5 text-[10px] font-black uppercase text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    FOTO DO SERVIÇO CONCLUÍDO (DEPOIS)
                                </span>
                                <div class="mt-3 aspect-video overflow-hidden rounded-xl bg-gray-100 dark:bg-gray-700">
                                    <img src="<?php echo e(asset('storage/' . $fotoDepois)); ?>" alt="Foto depois" class="h-full w-full object-cover">
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    <?php endif; ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.portal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Felipe\Desktop\LivreOS-main\LivreOS\sistema_livreos\resources\views/portal/zeladoria-status.blade.php ENDPATH**/ ?>