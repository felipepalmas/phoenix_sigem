{{--
 * Componente da aplicação Phoenix - SIGEM
 * Abertura Pública de Solicitação de Zeladoria Urbana & Ouvidoria
 *
 * @license AGPL-3.0
 --}}

@extends('layouts.portal')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    <!-- Breadcrumb e Título -->
    <div class="flex flex-col gap-2">
        <nav class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
            <a href="{{ route('portal.index') }}" class="hover:text-brand-600 transition">Portal do Cidadão</a>
            <span>/</span>
            <span class="text-gray-900 dark:text-white font-semibold">Ouvidoria & Zeladoria</span>
        </nav>
        <h1 class="text-2xl font-black text-gray-900 dark:text-white sm:text-3xl">
            Solicitar Serviço Público de Zeladoria
        </h1>
        <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300">
            Comunique problemas na sua rua ou bairro diretamente para a Secretaria de Obras e Serviços Públicos da Prefeitura.
        </p>
    </div>

    @if ($errors->any())
        <div class="rounded-2xl border border-red-300 bg-red-50 p-4 dark:border-red-800/60 dark:bg-red-900/20">
            <div class="flex items-start gap-3">
                <div class="rounded-lg bg-red-100 p-2 text-red-700 dark:bg-red-800/40 dark:text-red-300">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-red-900 dark:text-red-200">Atenção aos seguintes campos:</h3>
                    <ul class="mt-1 list-disc list-inside text-xs text-red-700 dark:text-red-300 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('portal.zeladoria.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" x-data="{ categoriaSelecionada: '{{ old('categoria_zeladoria_id', $categorias->first()->id ?? '') }}' }">
        @csrf

        <!-- Seção 1: Identificação do Cidadão -->
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800">
            <div class="flex items-center gap-3 border-b border-gray-100 pb-4 dark:border-gray-700 mb-5">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-100 font-bold text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 text-sm">
                    1
                </div>
                <div>
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white">Identificação do Munícipe</h2>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400">Seus dados são protegidos pela LGPD e usados para enviar atualizações do protocolo.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="nome" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
                        Nome Completo <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nome" id="nome" value="{{ old('nome') }}" placeholder="Ex: Maria dos Santos" required
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-xs text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>

                <div>
                    <label for="cpf" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
                        CPF do Solicitante <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="cpf" id="cpf" value="{{ old('cpf') }}" placeholder="000.000.000-00" required
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-xs font-mono text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>

                <div>
                    <label for="telefone" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
                        Telefone / WhatsApp <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="telefone" id="telefone" value="{{ old('telefone') }}" placeholder="(99) 99999-9999" required
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-xs text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>

                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
                        E-mail para Notificação (Opcional)
                    </label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="seu.email@exemplo.com"
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-xs text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>
            </div>
        </div>

        <!-- Seção 2: Categoria do Serviço Público -->
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800">
            <div class="flex items-center gap-3 border-b border-gray-100 pb-4 dark:border-gray-700 mb-5">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 font-bold text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 text-sm">
                    2
                </div>
                <div>
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white">Tipo de Serviço de Zeladoria</h2>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400">Escolha a categoria que melhor descreve a sua solicitação.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($categorias as $cat)
                    <label class="relative flex cursor-pointer rounded-2xl border p-4 transition"
                        :class="categoriaSelecionada == '{{ $cat->id }}' ? 'border-brand-500 bg-brand-50/50 dark:bg-brand-950/20 shadow-sm ring-2 ring-brand-500/20' : 'border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800 hover:border-gray-300'">
                        <input type="radio" name="categoria_zeladoria_id" value="{{ $cat->id }}" x-model="categoriaSelecionada" class="sr-only">
                        <div class="flex items-start gap-3 w-full">
                            <div class="rounded-xl p-2.5 shrink-0 {{ $cat->badge_color }}">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-gray-900 dark:text-white">{{ $cat->nome }}</span>
                                <span class="block text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 line-clamp-2">{{ $cat->descricao }}</span>
                                <span class="inline-block mt-2 font-mono text-[10px] text-brand-600 dark:text-brand-400 font-bold">
                                    Prazo estimado: {{ $cat->prazo_padrao_dias }} dias
                                </span>
                            </div>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Seção 3: Endereço & Localização -->
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800">
            <div class="flex items-center gap-3 border-b border-gray-100 pb-4 dark:border-gray-700 mb-5">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-purple-100 font-bold text-purple-700 dark:bg-purple-900/40 dark:text-purple-300 text-sm">
                    3
                </div>
                <div>
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white">Localização do Problema</h2>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400">Informe o local com precisão para facilitar a rota da equipe de campo.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="sm:col-span-2">
                    <label for="logradouro" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
                        Rua / Avenida / Praça <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="logradouro" id="logradouro" value="{{ old('logradouro') }}" placeholder="Ex: Av. Brasil, Rua XV de Novembro" required
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-xs text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>

                <div>
                    <label for="numero" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
                        Número / Em frente ao nº
                    </label>
                    <input type="text" name="numero" id="numero" value="{{ old('numero') }}" placeholder="Ex: 1450 ou S/N"
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-xs text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>

                <div>
                    <label for="bairro" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
                        Bairro <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="bairro" id="bairro" value="{{ old('bairro') }}" placeholder="Ex: Centro, Jardim América" required
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-xs text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>

                <div class="sm:col-span-2">
                    <label for="ponto_referencia" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
                        Ponto de Referência
                    </label>
                    <input type="text" name="ponto_referencia" id="ponto_referencia" value="{{ old('ponto_referencia') }}" placeholder="Ex: Próximo à padaria, em frente ao poste nº 42"
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-xs text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>
            </div>
        </div>

        <!-- Seção 4: Relato & Foto da Ocorrência -->
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800">
            <div class="flex items-center gap-3 border-b border-gray-100 pb-4 dark:border-gray-700 mb-5">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 font-bold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 text-sm">
                    4
                </div>
                <div>
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white">Descrição do Problema & Foto</h2>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400">Detalhe o que está acontecendo e, se possível, anexe uma foto da ocorrência.</p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label for="relato_cliente" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
                        Descreva o problema <span class="text-red-500">*</span>
                    </label>
                    <textarea name="relato_cliente" id="relato_cliente" rows="3" required placeholder="Ex: Lâmpada de iluminação pública queimada há cerca de 5 dias deixando a esquina às escuras..."
                        class="w-full rounded-2xl border border-gray-300 bg-white p-4 text-xs text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">{{ old('relato_cliente') }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
                        Anexar Foto da Ocorrência (Opcional - JPEG ou PNG)
                    </label>
                    <input type="file" name="foto" accept="image/*"
                        class="block w-full text-xs text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-gray-700 dark:file:text-gray-200">
                    <p class="text-[11px] text-gray-400 mt-1">A imagem ajuda a equipe a levar as peças e maquinários adequados.</p>
                </div>
            </div>
        </div>

        <!-- Botão de Envio -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('portal.index') }}" class="rounded-xl border border-gray-300 bg-white px-5 py-3 text-xs font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                Cancelar
            </a>
            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-7 py-3 text-xs font-bold text-white shadow-md hover:bg-brand-700 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Protocolar Solicitação Municipal
            </button>
        </div>
    </form>

</div>
@endsection
