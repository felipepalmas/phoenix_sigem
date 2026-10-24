{{--
 * Componente da aplicação Phoenix - SIGEM
 * Frota — cadastro de veículo ou máquina (padrão SIGEM Clean)
 *
 * @license AGPL-3.0
 --}}
@extends('layouts.app')

@section('content')
@php use App\Models\Veiculo; @endphp
<x-sigem.page-header title="Cadastrar veículo" subtitle="Inclua na frota um veículo, ambulância, ônibus escolar, caminhão ou máquina do patrimônio municipal."
    :breadcrumbs="['Frota' => route('frota.index'), 'Cadastrar veículo' => null]" />

<x-sigem.flash />

<form method="POST" action="{{ route('frota.store') }}" class="flex max-w-5xl flex-col gap-5">
    @csrf

    <x-sigem.card title="Identificação" id="t-ident" subtitle="Dados do documento do veículo (CRLV).">
        <div class="grid gap-4 md:grid-cols-4">
            <x-sigem.field label="Placa" for="placa" required help="Padrão Mercosul ou antigo, sem hífen.">
                <input id="placa" name="placa" type="text" required maxlength="10" value="{{ old('placa') }}" placeholder="BRA2E19" autocomplete="off"
                    class="sigem-input font-mono uppercase tracking-wide @error('placa') sigem-input-invalid @enderror" @error('placa') aria-describedby="placa-erro" @enderror>
            </x-sigem.field>
            <x-sigem.field label="Marca" for="marca" required>
                <input id="marca" name="marca" type="text" required maxlength="60" value="{{ old('marca') }}" placeholder="Ex.: Mercedes-Benz" class="sigem-input">
            </x-sigem.field>
            <x-sigem.field label="Modelo e versão" for="modelo" required class="md:col-span-2">
                <input id="modelo" name="modelo" type="text" required maxlength="100" value="{{ old('modelo') }}" placeholder="Ex.: Sprinter 415 CDI Ambulância" class="sigem-input">
            </x-sigem.field>
            <x-sigem.field label="Tipo" for="tipo" required class="md:col-span-2" help="Máquinas pesadas controlam horímetro (horas) em vez de quilômetros.">
                <select id="tipo" name="tipo" required class="sigem-input">
                    <option value="">Selecione o tipo</option>
                    @foreach(Veiculo::TIPOS as $valor => $rotulo)
                        <option value="{{ $valor }}" @selected(old('tipo') === $valor)>{{ $rotulo }}</option>
                    @endforeach
                </select>
            </x-sigem.field>
            <x-sigem.field label="Combustível" for="combustivel" required class="md:col-span-2">
                <select id="combustivel" name="combustivel" required class="sigem-input">
                    @foreach(Veiculo::COMBUSTIVEIS as $valor => $rotulo)
                        <option value="{{ $valor }}" @selected(old('combustivel', 'flex') === $valor)>{{ $rotulo }}</option>
                    @endforeach
                </select>
            </x-sigem.field>
            <x-sigem.field label="Ano de fabricação" for="ano_fabricacao" required>
                <input id="ano_fabricacao" name="ano_fabricacao" type="number" required min="1970" max="2030" value="{{ old('ano_fabricacao', date('Y')) }}" class="sigem-input">
            </x-sigem.field>
            <x-sigem.field label="Ano do modelo" for="ano_modelo" required>
                <input id="ano_modelo" name="ano_modelo" type="number" required min="1970" max="2030" value="{{ old('ano_modelo', date('Y')) }}" class="sigem-input">
            </x-sigem.field>
            <x-sigem.field label="Renavam" for="renavam">
                <input id="renavam" name="renavam" type="text" maxlength="30" inputmode="numeric" value="{{ old('renavam') }}" placeholder="Ex.: 00123456789" class="sigem-input">
            </x-sigem.field>
            <x-sigem.field label="Chassi" for="chassi">
                <input id="chassi" name="chassi" type="text" maxlength="40" value="{{ old('chassi') }}" placeholder="9BWZZZ377VT004251" class="sigem-input uppercase">
            </x-sigem.field>
        </div>
    </x-sigem.card>

    <x-sigem.card title="Lotação e capacidade" id="t-lotacao">
        <div class="grid gap-4 md:grid-cols-4">
            <x-sigem.field label="Secretaria" for="unidade_administrativa_id" class="md:col-span-2" help="Sem secretaria, o veículo fica na garagem central.">
                <select id="unidade_administrativa_id" name="unidade_administrativa_id" class="sigem-input">
                    <option value="">Garagem central</option>
                    @foreach($secretarias as $s)
                        <option value="{{ $s->id }}" @selected((string) old('unidade_administrativa_id') === (string) $s->id)>{{ $s->sigla ? $s->sigla . ' — ' : '' }}{{ $s->nome }}</option>
                    @endforeach
                </select>
            </x-sigem.field>
            <x-sigem.field label="Nº de tombamento (patrimônio)" for="patrimonio_tombamento" class="md:col-span-2">
                <input id="patrimonio_tombamento" name="patrimonio_tombamento" type="text" maxlength="40" value="{{ old('patrimonio_tombamento') }}" placeholder="Ex.: PAT-2026-089" class="sigem-input">
            </x-sigem.field>
            <x-sigem.field label="Hodômetro atual" for="km_atual" required help="Em km; para máquinas, horas no horímetro.">
                <input id="km_atual" name="km_atual" type="number" required min="0" step="1" value="{{ old('km_atual', 0) }}" class="sigem-input tabular-nums">
            </x-sigem.field>
            <x-sigem.field label="Capacidade do tanque (L)" for="capacidade_tanque" help="Usada para bloquear abastecimentos acima do tanque.">
                <input id="capacidade_tanque" name="capacidade_tanque" type="number" min="0" step="0.1" value="{{ old('capacidade_tanque') }}" placeholder="Ex.: 55" class="sigem-input">
            </x-sigem.field>
            <x-sigem.field label="Lugares (com motorista)" for="capacidade_passageiros">
                <input id="capacidade_passageiros" name="capacidade_passageiros" type="number" min="1" step="1" value="{{ old('capacidade_passageiros') }}" placeholder="Ex.: 5" class="sigem-input">
            </x-sigem.field>
            <x-sigem.field label="Observações" for="observacoes" class="md:col-span-4" help="Equipamentos especiais (sirene, rádio, engate, giroflex) e restrições de uso.">
                <textarea id="observacoes" name="observacoes" rows="3" class="sigem-input">{{ old('observacoes') }}</textarea>
            </x-sigem.field>
        </div>
    </x-sigem.card>

    <div class="flex flex-wrap justify-end gap-2">
        <x-sigem.button href="{{ route('frota.index') }}">Cancelar</x-sigem.button>
        <x-sigem.button type="submit" variant="primary">Cadastrar veículo</x-sigem.button>
    </div>
</form>
@endsection
