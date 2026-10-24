@extends('layouts.app')

@section('content')
<x-sigem.page-header :title="'Editar ' . ($unidade->sigla ?: $unidade->nome)" :subtitle="$unidade->nome"
    :breadcrumbs="['Secretarias' => route('secretarias.index'), ($unidade->sigla ?: $unidade->nome) => null]" />
<x-sigem.flash />
<form method="POST" action="{{ route('secretarias.update', $unidade) }}" class="flex max-w-4xl flex-col gap-5">
    @csrf
    @method('PUT')
    @include('secretarias.partials.form')
    <div class="flex flex-wrap justify-between gap-2">
        <span></span>
        <div class="flex gap-2">
            <x-sigem.button href="{{ route('secretarias.index') }}">Cancelar</x-sigem.button>
            <x-sigem.button type="submit" variant="primary">Salvar alterações</x-sigem.button>
        </div>
    </div>
</form>
<form method="POST" action="{{ route('secretarias.destroy', $unidade) }}" class="mt-6 max-w-4xl" onsubmit="return confirm('Excluir esta unidade? Só é possível quando não há subunidades nem processos nela.');">
    @csrf
    @method('DELETE')
    <x-sigem.card title="Excluir unidade" id="t-excluir" subtitle="Prefira desativar: a exclusão só é aceita sem subunidades e sem processos tramitando.">
        <x-sigem.button type="submit" variant="danger">Excluir unidade</x-sigem.button>
    </x-sigem.card>
</form>
@endsection
