{{--
 * Componente da aplicação Phoenix - SIGEM (Zero Papel)
 * Autuação de Novo Processo Digital
 *
 * @license AGPL-3.0
 --}}

@extends('layouts.app')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
            <a href="{{ route('protocolo.index') }}" class="hover:underline">Protocolo Digital</a>
            <span>/</span>
            <span>Novo Processo</span>
        </div>
        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Autuar Novo Processo Administrativo</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">Preencha os dados do requerimento para gerar o número de protocolo oficial</p>
    </div>
    <a href="{{ route('protocolo.index') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
        Voltar à Lista
    </a>
</div>

@if ($errors->any())
<div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/30 dark:text-red-300">
    <p class="font-bold mb-1">Por favor, corrija os erros abaixo:</p>
    <ul class="list-disc pl-5 space-y-1">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form action="{{ route('protocolo.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
    @csrf

    <!-- Card 1: Dados do Requerente / Interessado -->
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-100 text-xs font-bold text-brand-700 dark:bg-brand-900 dark:text-brand-300">1</span>
            Cidadão / Contribuinte Interessado
        </h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="sm:col-span-2">
                <label for="interessado_id" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Selecione o Cidadão / Munícipe / Empresa <span class="text-red-500">*</span>
                </label>
                <select id="interessado_id" name="interessado_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:border-brand-500">
                    <option value="">-- Selecione o Cidadão / Contribuinte --</option>
                    @foreach($cidadaos as $cidadao)
                        <option value="{{ $cidadao->id }}" {{ old('interessado_id') == $cidadao->id ? 'selected' : '' }}>
                            {{ $cidadao->nome }} ({{ $cidadao->documento_principal ?? 'Sem Doc' }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <a href="{{ route('clientes.create') }}" target="_blank" class="w-full inline-flex items-center justify-center gap-1.5 rounded-lg border border-dashed border-brand-400 bg-brand-50/50 px-4 py-2.5 text-sm font-medium text-brand-700 hover:bg-brand-100 dark:border-brand-600 dark:bg-brand-900/20 dark:text-brand-300">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Cadastrar Novo Cidadão
                </a>
            </div>
        </div>
    </div>

    <!-- Card 2: Dados do Processo -->
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-100 text-xs font-bold text-brand-700 dark:bg-brand-900 dark:text-brand-300">2</span>
            Dados do Processo & Destinação
        </h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="assunto" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Assunto do Requerimento <span class="text-red-500">*</span>
                </label>
                <input type="text" id="assunto" name="assunto" value="{{ old('assunto') }}" required placeholder="Ex: Solicitação de Alvará Sanitário, Recurso de Multa, Poda de Árvore..." class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:border-brand-500">
            </div>

            <div>
                <label for="unidade_atual_id" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Secretaria / Departamento de Destino <span class="text-red-500">*</span>
                </label>
                <select id="unidade_atual_id" name="unidade_atual_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:border-brand-500">
                    <option value="">-- Selecione a Unidade Responsável --</option>
                    @foreach($unidades as $unidade)
                        <option value="{{ $unidade->id }}" {{ old('unidade_atual_id') == $unidade->id ? 'selected' : '' }}>
                            {{ $unidade->nome_completo }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="prioridade" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Grau de Prioridade <span class="text-red-500">*</span>
                </label>
                <select id="prioridade" name="prioridade" required class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    <option value="normal" {{ old('prioridade', 'normal') === 'normal' ? 'selected' : '' }}>Normal (Trâmite Padrão)</option>
                    <option value="urgente" {{ old('prioridade') === 'urgente' ? 'selected' : '' }}>Urgente (Prazo Reduzido / Prioritário)</option>
                    <option value="baixa" {{ old('prioridade') === 'baixa' ? 'selected' : '' }}>Baixa</option>
                </select>
            </div>

            <div>
                <label for="sigilo" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Nível de Acesso / Sigilo <span class="text-red-500">*</span>
                </label>
                <select id="sigilo" name="sigilo" required class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    <option value="publico" {{ old('sigilo', 'publico') === 'publico' ? 'selected' : '' }}>Público (Consulta Livre / Transparência)</option>
                    <option value="restrito" {{ old('sigilo') === 'restrito' ? 'selected' : '' }}>Restrito (Interessado e Servidores)</option>
                    <option value="sigiloso" {{ old('sigilo') === 'sigiloso' ? 'selected' : '' }}>Sigiloso (Informações Protegidas)</option>
                </select>
            </div>

            <div>
                <label for="prazo_dias" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Prazo Legal para Resposta (em dias)
                </label>
                <input type="number" id="prazo_dias" name="prazo_dias" value="{{ old('prazo_dias', 15) }}" min="1" max="365" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>

            <div class="sm:col-span-2">
                <label for="descricao" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Descrição Detalhada / Motivo do Requerimento
                </label>
                <textarea id="descricao" name="descricao" rows="3" placeholder="Informações complementares sobre o pedido do cidadão..." class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:border-brand-500">{{ old('descricao') }}</textarea>
            </div>
        </div>
    </div>

    <!-- Card 3: Documento / Peça Inicial (Upload com SHA-256) -->
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-100 text-xs font-bold text-brand-700 dark:bg-brand-900 dark:text-brand-300">3</span>
            Peça Inicial & Anexos do Cidadão (Zero Papel)
        </h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="titulo_documento" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Identificação da Peça Inicial
                </label>
                <input type="text" id="titulo_documento" name="titulo_documento" value="{{ old('titulo_documento', 'Requerimento Inicial do Cidadão') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>
            <div>
                <label for="documento_inicial" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Arquivo Digital (PDF, Imagem, Documento)
                </label>
                <input type="file" id="documento_inicial" name="documento_inicial" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                <p class="mt-1 text-xs text-gray-500">O arquivo receberá carimbo de integridade com Hash SHA-256 no envio.</p>
            </div>
        </div>
    </div>

    <!-- Botões de Ação -->
    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('protocolo.index') }}" class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
            Cancelar
        </a>
        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow hover:bg-brand-700 transition">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Autuar e Iniciar Tramitação
        </button>
    </div>
</form>
@endsection
