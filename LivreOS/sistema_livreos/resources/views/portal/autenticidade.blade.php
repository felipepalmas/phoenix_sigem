{{--
 * Componente da aplicação Phoenix - SIGEM
 * Validador Público de Autenticidade e Integridade Documental (Hash SHA-256)
 *
 * @license AGPL-3.0
 --}}

@extends('layouts.portal')

@section('content')
<div class="space-y-8 max-w-3xl mx-auto">

    <!-- Breadcrumb e Título -->
    <div class="text-center">
        <nav class="flex justify-center items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-2">
            <a href="{{ route('portal.index') }}" class="hover:text-brand-600 transition">Portal do Cidadão</a>
            <span>/</span>
            <span class="text-gray-900 dark:text-white font-semibold">Autenticidade Documental</span>
        </nav>
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 mb-3">
            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        </div>
        <h1 class="text-2xl font-black text-gray-900 dark:text-white sm:text-3xl">
            Verificador de Autenticidade Documental
        </h1>
        <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 mt-2 max-w-lg mx-auto">
            Confira a validade jurídica de certidões, despachos e pareceres municipais através da conferência da chave criptográfica SHA-256.
        </p>
    </div>

    <!-- Formulário de Verificação de Hash -->
    <div class="rounded-3xl border border-gray-200 bg-white p-6 sm:p-8 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800">
        <form action="{{ route('portal.autenticidade') }}" method="GET" class="space-y-4">
            <div>
                <label for="hash" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                    Hash Criptográfico SHA-256 ou Código do Documento
                </label>
                <div class="relative">
                    <input type="text" name="hash" id="hash" value="{{ $termo ?? '' }}" placeholder="Ex: 8a4c1f6d89e2b0... ou ID da peça" required
                        class="w-full rounded-2xl border border-gray-300 bg-gray-50 px-4 py-3 text-xs sm:text-sm font-mono text-gray-900 focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>
                <p class="text-[11px] text-gray-500 mt-1.5">
                    O código SHA-256 de 64 caracteres encontra-se impresso no rodapé ou QR Code do documento oficial.
                </p>
            </div>

            <button type="submit" class="w-full rounded-xl bg-emerald-600 py-3 text-xs sm:text-sm font-bold text-white shadow-md hover:bg-emerald-700 transition">
                Validar Integridade do Documento
            </button>
        </form>
    </div>

    <!-- Resultado da Verificação -->
    @if(isset($resultado))
        @if($resultado['autentico'])
            <!-- DOCUMENTO VÁLIDO E AUTÊNTICO -->
            <div class="rounded-3xl border border-emerald-300 bg-emerald-50/70 p-6 sm:p-8 shadow-theme-sm dark:border-emerald-700/60 dark:bg-emerald-950/20 space-y-6">
                <div class="flex items-center gap-4 border-b border-emerald-200 pb-5 dark:border-emerald-800">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-sm">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <span class="rounded bg-emerald-200 px-2 py-0.5 font-mono text-[10px] font-black uppercase text-emerald-900 dark:bg-emerald-900 dark:text-emerald-200">
                            VERIFICAÇÃO POSITIVA
                        </span>
                        <h2 class="text-lg font-black text-emerald-950 dark:text-emerald-100 mt-0.5">
                            Documento Autêntico e Íntegro
                        </h2>
                        <p class="text-xs text-emerald-800 dark:text-emerald-300">
                            A assinatura digital e a chave hash conferem com o registro nos arquivos oficiais da Prefeitura.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 text-xs">
                    <div class="rounded-2xl bg-white p-4 dark:bg-gray-800 border border-emerald-100 dark:border-gray-700">
                        <span class="text-gray-400 block text-[11px] uppercase tracking-wider font-bold mb-1">Título da Peça:</span>
                        <strong class="text-gray-900 dark:text-white">{{ $resultado['documento']->titulo }}</strong>
                        <p class="text-[11px] text-gray-500 mt-1">Tipo: {{ ucfirst($resultado['documento']->tipo_documento ?? 'Documento') }}</p>
                    </div>

                    <div class="rounded-2xl bg-white p-4 dark:bg-gray-800 border border-emerald-100 dark:border-gray-700">
                        <span class="text-gray-400 block text-[11px] uppercase tracking-wider font-bold mb-1">Processo Vinculado:</span>
                        @if(!empty($resultado['is_sigiloso']))
                            <strong class="font-mono text-amber-600 dark:text-amber-400">Processo com Sigilo Legal (LAI)</strong>
                            <p class="text-[11px] text-amber-700 dark:text-amber-400 mt-1">Metadados protegidos nos termos da Lei nº 12.527/2011.</p>
                        @else
                            <strong class="font-mono text-brand-600 dark:text-brand-400">{{ $resultado['processo']->numero ?? 'Processo Administrativo' }}</strong>
                            <p class="text-[11px] text-gray-500 mt-1 truncate">{{ $resultado['processo']->assunto ?? 'Assunto não informado' }}</p>
                        @endif
                    </div>

                    <div class="rounded-2xl bg-white p-4 dark:bg-gray-800 border border-emerald-100 dark:border-gray-700">
                        <span class="text-gray-400 block text-[11px] uppercase tracking-wider font-bold mb-1">Emissão Oficial:</span>
                        <strong class="text-gray-900 dark:text-white">{{ $resultado['data_emissao']->format('d/m/Y \à\s H:i:s') }}</strong>
                        <p class="text-[11px] text-gray-500 mt-1">Servidor: {{ $resultado['assinado_por'] }}</p>
                    </div>

                    <div class="rounded-2xl bg-white p-4 dark:bg-gray-800 border border-emerald-100 dark:border-gray-700">
                        <span class="text-gray-400 block text-[11px] uppercase tracking-wider font-bold mb-1">Status da Assinatura:</span>
                        <span class="inline-flex items-center gap-1 font-bold text-emerald-600 dark:text-emerald-400">
                            ✓ Assinatura Eletrônica Ativa (Lei 14.063/2020)
                        </span>
                    </div>
                </div>

                <div class="rounded-2xl bg-white p-4 dark:bg-gray-800 border border-emerald-100 dark:border-gray-700 text-xs">
                    <span class="text-gray-400 block text-[11px] uppercase tracking-wider font-bold mb-1">Hash SHA-256 Auditado:</span>
                    <p class="font-mono text-[11px] text-gray-800 dark:text-gray-200 break-all select-all bg-gray-50 dark:bg-gray-900 p-2.5 rounded-xl border border-gray-100 dark:border-gray-800">
                        {{ $resultado['hash'] }}
                    </p>
                </div>
            </div>
        @else
            <!-- DOCUMENTO NÃO CONFERE -->
            <div class="rounded-3xl border border-red-300 bg-red-50 p-6 sm:p-8 dark:border-red-800/60 dark:bg-red-950/20 text-center space-y-3">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-red-600 text-white shadow-sm mb-2">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <h3 class="text-base font-bold text-red-900 dark:text-red-200">
                    Documento Não Encontrado ou Alterado
                </h3>
                <p class="text-xs text-red-800 dark:text-red-300 max-w-md mx-auto leading-relaxed">
                    {{ $resultado['mensagem'] }}
                </p>
                <p class="text-[11px] text-gray-500 font-mono mt-1">Hash pesquisado: {{ $resultado['hash'] }}</p>
            </div>
        @endif
    @endif

</div>
@endsection
