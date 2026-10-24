{{--
 * Componente da aplicação Phoenix - SIGEM
 * Edição de Secretaria / Unidade Administrativa
 *
 * @license AGPL-3.0
 --}}

@extends('layouts.app')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
        <a href="{{ route('secretarias.index') }}" class="hover:underline">Secretarias</a>
        <span>/</span>
        <span class="text-gray-700 dark:text-gray-300">Editar {{ $unidade->sigla ?: $unidade->nome }}</span>
    </div>
    <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Editar Unidade Administrativa</h1>
    <p class="text-sm text-gray-600 dark:text-gray-400">Atualize os dados e atribuições da unidade no organograma da Prefeitura</p>
</div>

@if($errors->any())
<div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/30 dark:text-red-300">
    <ul class="list-disc pl-5 space-y-1">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form action="{{ route('secretarias.update', $unidade) }}" method="POST" class="max-w-4xl space-y-6">
    @csrf
    @method('PUT')

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800 space-y-5">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-700 pb-3">Identificação da Unidade</h2>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="sm:col-span-2">
                <label for="nome" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Nome Oficial da Unidade <span class="text-red-500">*</span></label>
                <input type="text" id="nome" name="nome" value="{{ old('nome', $unidade->nome) }}" required maxlength="200" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>

            <div>
                <label for="sigla" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Sigla Oficial</label>
                <input type="text" id="sigla" name="sigla" value="{{ old('sigla', $unidade->sigla) }}" maxlength="30" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm uppercase dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label for="tipo" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de Unidade <span class="text-red-500">*</span></label>
                <select id="tipo" name="tipo" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    <option value="secretaria" {{ old('tipo', $unidade->tipo) == 'secretaria' ? 'selected' : '' }}>Secretaria Municipal</option>
                    <option value="gabinete" {{ old('tipo', $unidade->tipo) == 'gabinete' ? 'selected' : '' }}>Gabinete do Prefeito / Vice</option>
                    <option value="departamento" {{ old('tipo', $unidade->tipo) == 'departamento' ? 'selected' : '' }}>Departamento</option>
                    <option value="divisao" {{ old('tipo', $unidade->tipo) == 'divisao' ? 'selected' : '' }}>Divisão</option>
                    <option value="setor" {{ old('tipo', $unidade->tipo) == 'setor' ? 'selected' : '' }}>Setor / Núcleo</option>
                </select>
            </div>

            <div>
                <label for="parent_id" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Subordinada a (Órgão Superior)</label>
                <select id="parent_id" name="parent_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    <option value="">Nenhum (Órgão de Cúpula / Secretaria Principal)</option>
                    @foreach($unidadesPai as $pai)
                        <option value="{{ $pai->id }}" {{ old('parent_id', $unidade->parent_id) == $pai->id ? 'selected' : '' }}>
                            {{ $pai->sigla ? "{$pai->sigla} - " : '' }}{{ $pai->nome }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="ordem" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Ordem de Exibição</label>
                <input type="number" id="ordem" name="ordem" value="{{ old('ordem', $unidade->ordem) }}" min="0" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>
        </div>

        <h2 class="text-base font-semibold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-700 pb-3 pt-3">Responsável e Contato</h2>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label for="responsavel_id" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Secretário(a) / Responsável</label>
                <select id="responsavel_id" name="responsavel_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    <option value="">Selecione um servidor oficial...</option>
                    @foreach($usuarios as $user)
                        <option value="{{ $user->id }}" {{ old('responsavel_id', $unidade->responsavel_id) == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="email" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">E-mail Institucional</label>
                <input type="email" id="email" name="email" value="{{ old('email', $unidade->email) }}" maxlength="150" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>

            <div>
                <label for="telefone" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Telefone / Ramal</label>
                <input type="text" id="telefone" name="telefone" value="{{ old('telefone', $unidade->telefone) }}" maxlength="50" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>
        </div>

        <div class="pt-2">
            <label class="inline-flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="ativo" value="1" {{ old('ativo', $unidade->ativo) ? 'checked' : '' }} class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Unidade Administrativa Ativa no Município</span>
            </label>
        </div>
    </div>

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 transition">
                Atualizar Secretaria
            </button>
            <a href="{{ route('secretarias.index') }}" class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition">
                Cancelar
            </a>
        </div>
    </div>
</form>

<div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700 max-w-4xl">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-sm font-semibold text-red-600 dark:text-red-400">Excluir Unidade Administrativa</h3>
            <p class="text-xs text-gray-500">A exclusão só é permitida se não houver processos ou subunidades vinculadas.</p>
        </div>
        <form action="{{ route('secretarias.destroy', $unidade) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta unidade administrativa?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="rounded-lg border border-red-300 bg-red-50 px-3.5 py-2 text-xs font-semibold text-red-700 hover:bg-red-100 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300 transition">
                Excluir Unidade
            </button>
        </form>
    </div>
</div>
@endsection
