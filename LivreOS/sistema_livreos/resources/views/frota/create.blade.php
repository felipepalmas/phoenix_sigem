{{--
 * Componente da aplicação Phoenix - SIGEM
 * Cadastro de Veículo / Máquina da Frota Municipal
 *
 * @license AGPL-3.0
 --}}

@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Breadcrumb e Cabeçalho -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <nav class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-1">
                <a href="{{ route('frota.index') }}" class="hover:text-brand-600 transition">Frota Municipal</a>
                <span>/</span>
                <span class="text-gray-900 dark:text-white font-semibold">Novo Veículo ou Máquina</span>
            </nav>
            <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white sm:text-3xl">
                Cadastrar Veículo na Frota Oficial
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Inclusão de viaturas, ambulâncias, ônibus escolares, caminhões e maquinários pesados ao patrimônio municipal.
            </p>
        </div>

        <a href="{{ route('frota.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-xs font-bold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Voltar à Garagem
        </a>
    </div>

    @if ($errors->any())
        <div class="rounded-xl border border-red-300 bg-red-50 p-4 dark:border-red-800/60 dark:bg-red-900/20">
            <div class="flex items-start gap-3">
                <div class="rounded-lg bg-red-100 p-2 text-red-700 dark:bg-red-800/40 dark:text-red-300">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-red-900 dark:text-red-200">Verifique os campos obrigatórios:</h3>
                    <ul class="mt-1 list-disc list-inside text-xs text-red-700 dark:text-red-300 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Formulário Principal -->
    <form action="{{ route('frota.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Seção 1: Identificação do Veículo -->
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800">
            <div class="flex items-center gap-3 border-b border-gray-100 pb-4 dark:border-gray-700 mb-6">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 font-bold">
                    1
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Identificação & Dados de Emplacamento</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Dados do veículo registrados no DETRAN e no Patrimônio da Prefeitura.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-4">
                <!-- Placa -->
                <div>
                    <label for="placa" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                        Placa Oficial <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="placa" id="placa" value="{{ old('placa') }}" placeholder="Ex: BRA2E19" required maxlength="10"
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-mono uppercase tracking-wider text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder-gray-500">
                    <p class="text-[11px] text-gray-500 mt-1">Padrão Mercosul ou cinza.</p>
                </div>

                <!-- Marca -->
                <div>
                    <label for="marca" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                        Marca / Fabricante <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="marca" id="marca" value="{{ old('marca') }}" placeholder="Ex: Mercedes-Benz, Volkswagen" required
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder-gray-500">
                </div>

                <!-- Modelo -->
                <div class="md:col-span-2">
                    <label for="modelo" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                        Modelo & Versão <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="modelo" id="modelo" value="{{ old('modelo') }}" placeholder="Ex: Sprinter 415 CDI SAMU UTI Móvel" required
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder-gray-500">
                </div>

                <!-- Tipo de Veículo -->
                <div>
                    <label for="tipo" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                        Tipo de Veículo / Função <span class="text-red-500">*</span>
                    </label>
                    <select name="tipo" id="tipo" required
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Selecione o tipo...</option>
                        <option value="ambulancia" {{ old('tipo') == 'ambulancia' ? 'selected' : '' }}>Ambulância / UTI Móvel</option>
                        <option value="onibus_escolar" {{ old('tipo') == 'onibus_escolar' ? 'selected' : '' }}>Ônibus / Transporte Escolar</option>
                        <option value="caminhao" {{ old('tipo') == 'caminhao' ? 'selected' : '' }}>Caminhão Basculante / Pipa / Coleta</option>
                        <option value="maquina_pesada" {{ old('tipo') == 'maquina_pesada' ? 'selected' : '' }}>Máquina Pesada / Trator / Retroescavadeira</option>
                        <option value="automovel" {{ old('tipo') == 'automovel' ? 'selected' : '' }}>Automóvel / Sedan / Hatch Oficial</option>
                        <option value="caminhonete" {{ old('tipo') == 'caminhonete' ? 'selected' : '' }}>Caminhonete / Utilitário 4x4</option>
                        <option value="van" {{ old('tipo') == 'van' ? 'selected' : '' }}>Van / Micro-ônibus de Passageiros</option>
                        <option value="motocicleta" {{ old('tipo') == 'motocicleta' ? 'selected' : '' }}>Motocicleta / Guarda Municipal / Trânsito</option>
                    </select>
                </div>

                <!-- Combustível -->
                <div>
                    <label for="combustivel" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                        Combustível Principal <span class="text-red-500">*</span>
                    </label>
                    <select name="combustivel" id="combustivel" required
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="Diesel S10" {{ old('combustivel') == 'Diesel S10' ? 'selected' : '' }}>Diesel S10</option>
                        <option value="Diesel Comum" {{ old('combustivel') == 'Diesel Comum' ? 'selected' : '' }}>Diesel Comum</option>
                        <option value="Flex (Gas/Eta)" {{ old('combustivel', 'Flex (Gas/Eta)') == 'Flex (Gas/Eta)' ? 'selected' : '' }}>Flex (Gasolina / Etanol)</option>
                        <option value="Gasolina" {{ old('combustivel') == 'Gasolina' ? 'selected' : '' }}>Gasolina Comum</option>
                        <option value="Etanol" {{ old('combustivel') == 'Etanol' ? 'selected' : '' }}>Etanol</option>
                        <option value="Elétrico" {{ old('combustivel') == 'Elétrico' ? 'selected' : '' }}>Elétrico / Híbrido</option>
                    </select>
                </div>

                <!-- Ano Fabricação -->
                <div>
                    <label for="ano_fabricacao" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                        Ano Fabricação <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="ano_fabricacao" id="ano_fabricacao" value="{{ old('ano_fabricacao', date('Y')) }}" min="1980" max="{{ date('Y') + 1 }}" required
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>

                <!-- Ano Modelo -->
                <div>
                    <label for="ano_modelo" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                        Ano Modelo <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="ano_modelo" id="ano_modelo" value="{{ old('ano_modelo', date('Y')) }}" min="1980" max="{{ date('Y') + 2 }}" required
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>
            </div>
        </div>

        <!-- Seção 2: Lotação Administrativa & Patrimônio -->
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800">
            <div class="flex items-center gap-3 border-b border-gray-100 pb-4 dark:border-gray-700 mb-6">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300 font-bold">
                    2
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Lotação Administrativa & Documentação</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Secretaria responsável, tombamento de patrimônio e números de registro.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                <!-- Secretaria / Unidade -->
                <div>
                    <label for="unidade_administrativa_id" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                        Secretaria / Órgão Vinculado
                    </label>
                    <select name="unidade_administrativa_id" id="unidade_administrativa_id"
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Garagem Central / Geral</option>
                        @foreach ($secretarias as $sec)
                            <option value="{{ $sec->id }}" {{ old('unidade_administrativa_id') == $sec->id ? 'selected' : '' }}>
                                {{ $sec->sigla ? $sec->sigla . ' - ' : '' }}{{ $sec->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Patrimônio / Tombamento -->
                <div>
                    <label for="patrimonio_tombamento" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                        Nº de Tombamento (Patrimônio)
                    </label>
                    <input type="text" name="patrimonio_tombamento" id="patrimonio_tombamento" value="{{ old('patrimonio_tombamento') }}" placeholder="Ex: PAT-2026-089"
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder-gray-500">
                </div>

                <!-- Km Atual -->
                <div>
                    <label for="km_atual" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                        Quilometragem Atual (Km) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="km_atual" id="km_atual" value="{{ old('km_atual', 0) }}" min="0" required
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>

                <!-- RENAVAM -->
                <div>
                    <label for="renavam" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                        Código RENAVAM
                    </label>
                    <input type="text" name="renavam" id="renavam" value="{{ old('renavam') }}" placeholder="Ex: 00123456789"
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder-gray-500">
                </div>

                <!-- Chassi -->
                <div>
                    <label for="chassi" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                        Número do Chassi
                    </label>
                    <input type="text" name="chassi" id="chassi" value="{{ old('chassi') }}" placeholder="Ex: 9BWZZZ377VT004251"
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm uppercase text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder-gray-500">
                </div>

                <!-- Capacidade do Tanque e Passageiros -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="capacidade_tanque" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                            Tanque (L)
                        </label>
                        <input type="number" step="0.1" name="capacidade_tanque" id="capacidade_tanque" value="{{ old('capacidade_tanque', 55) }}" min="0"
                            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label for="capacidade_passageiros" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                            Passageiros
                        </label>
                        <input type="number" name="capacidade_passageiros" id="capacidade_passageiros" value="{{ old('capacidade_passageiros', 5) }}" min="1"
                            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                </div>
            </div>

            <!-- Observações Técnicas -->
            <div class="mt-5">
                <label for="observacoes" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                    Observações e Equipamentos Especiais (Ex: Sirene, Rádio VHF, Engate, Giroflex)
                </label>
                <textarea name="observacoes" id="observacoes" rows="3" placeholder="Informações adicionais relevantes para a garagem e fiscalização..."
                    class="w-full rounded-xl border border-gray-300 bg-white p-4 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder-gray-500">{{ old('observacoes') }}</textarea>
            </div>
        </div>

        <!-- Botões de Ação -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('frota.index') }}" class="rounded-xl border border-gray-300 bg-white px-6 py-3 text-xs font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition">
                Cancelar
            </a>
            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-7 py-3 text-xs font-bold text-white shadow-sm hover:bg-brand-700 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Confirmar e Cadastrar Veículo
            </button>
        </div>
    </form>

</div>
@endsection
