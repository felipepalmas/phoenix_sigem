@extends('layouts.app')

@section('content')
<x-sigem.page-header title="Nova secretaria ou setor" :breadcrumbs="['Secretarias' => route('secretarias.index'), 'Nova' => null]" />
<x-sigem.flash />
<form method="POST" action="{{ route('secretarias.store') }}" class="flex max-w-4xl flex-col gap-5">
    @csrf
    @include('secretarias.partials.form')
    <div class="flex justify-end gap-2">
        <x-sigem.button href="{{ route('secretarias.index') }}">Cancelar</x-sigem.button>
        <x-sigem.button type="submit" variant="primary">Cadastrar</x-sigem.button>
    </div>
</form>
@endsection
