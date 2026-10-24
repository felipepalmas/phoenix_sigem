{{--
 * Componente da aplicação Phoenix - SIGEM (Zero Papel)
 * Protocolo — autuação de processo (padrão SIGEM Clean)
 *
 * @license AGPL-3.0
 --}}
@extends('layouts.app')

@section('content')
@php $lotacao = auth()->user()->unidade_administrativa_id; @endphp
<x-sigem.page-header title="Autuar processo" subtitle="Registra o pedido, gera o número oficial e envia à secretaria responsável."
    :breadcrumbs="['Protocolo' => route('protocolo.index'), 'Autuar processo' => null]" />

<x-sigem.flash />

<form method="POST" action="{{ route('protocolo.store') }}" enctype="multipart/form-data" class="flex max-w-4xl flex-col gap-5">
    @csrf

    <x-sigem.card title="Quem pede e o quê" id="t-pedido">
        <div class="flex flex-col gap-4">
            <x-sigem.field label="Interessado" for="interessado_id" required
                help="Cidadão ou empresa já cadastrados. Não encontrou? Cadastre em Cidadãos e volte aqui.">
                <select id="interessado_id" name="interessado_id" required class="sigem-input">
                    <option value="">Selecione o interessado</option>
                    @foreach($cidadaos as $c)
                        @php $doc = $c->cpf ?: $c->cnpj; @endphp
                        <option value="{{ $c->id }}" @selected((string) old('interessado_id') === (string) $c->id)>{{ $c->nome ?: $c->razao_social }}{{ $doc ? ' — ' . $doc : '' }}</option>
                    @endforeach
                </select>
            </x-sigem.field>
            <x-sigem.field label="Assunto" for="assunto" required help="Uma linha que identifique o pedido. Ex.: Alvará sanitário para padaria.">
                <input id="assunto" name="assunto" type="text" required maxlength="255" value="{{ old('assunto') }}" class="sigem-input">
            </x-sigem.field>
            <x-sigem.field label="Descrição" for="descricao" help="Detalhes do pedido, como o interessado relatou.">
                <textarea id="descricao" name="descricao" rows="4" class="sigem-input">{{ old('descricao') }}</textarea>
            </x-sigem.field>
        </div>
    </x-sigem.card>

    <x-sigem.card title="Para onde vai" id="t-destino">
        <div class="grid gap-4 md:grid-cols-2">
            <x-sigem.field label="Secretaria responsável" for="unidade_atual_id" required class="md:col-span-2">
                <select id="unidade_atual_id" name="unidade_atual_id" required class="sigem-input">
                    <option value="">Selecione a secretaria</option>
                    @foreach($unidades as $u)
                        <option value="{{ $u->id }}" @selected((string) old('unidade_atual_id', $lotacao) === (string) $u->id)>{{ $u->sigla ? $u->sigla . ' — ' : '' }}{{ $u->nome }}</option>
                    @endforeach
                </select>
            </x-sigem.field>
            <x-sigem.field label="Prioridade" for="prioridade" required>
                <select id="prioridade" name="prioridade" required class="sigem-input">
                    <option value="normal" @selected(old('prioridade', 'normal') === 'normal')>Normal</option>
                    <option value="urgente" @selected(old('prioridade') === 'urgente')>Urgente</option>
                    <option value="baixa" @selected(old('prioridade') === 'baixa')>Baixa</option>
                </select>
            </x-sigem.field>
            <x-sigem.field label="Prazo de resposta (dias)" for="prazo_dias" help="Deixe em branco se não houver prazo.">
                <input id="prazo_dias" name="prazo_dias" type="number" min="1" max="365" value="{{ old('prazo_dias', 15) }}" class="sigem-input">
            </x-sigem.field>
            <x-sigem.field label="Acesso" for="sigilo" required class="md:col-span-2"
                help="Público: consulta pelo portal com número e chave. Restrito/sigiloso: só a secretaria atual e quem tem permissão (LAI, art. 23).">
                <select id="sigilo" name="sigilo" required class="sigem-input md:max-w-sm">
                    <option value="publico" @selected(old('sigilo', 'publico') === 'publico')>Público</option>
                    <option value="restrito" @selected(old('sigilo') === 'restrito')>Restrito</option>
                    <option value="sigiloso" @selected(old('sigilo') === 'sigiloso')>Sigiloso</option>
                </select>
            </x-sigem.field>
        </div>
    </x-sigem.card>

    <x-sigem.card title="Documento inicial (opcional)" id="t-doc">
        <div class="grid gap-4 md:grid-cols-2">
            <x-sigem.field label="Título do documento" for="titulo_documento">
                <input id="titulo_documento" name="titulo_documento" type="text" maxlength="255" value="{{ old('titulo_documento', 'Requerimento inicial') }}" class="sigem-input">
            </x-sigem.field>
            <x-sigem.field label="Arquivo" for="documento_inicial" help="PDF, imagem ou documento, até 20 MB.">
                <input id="documento_inicial" name="documento_inicial" type="file" class="sigem-input h-auto py-1.5" accept=".pdf,.png,.jpg,.jpeg,.webp,.odt,.docx,.doc,.txt,.xls,.xlsx,.csv">
            </x-sigem.field>
        </div>
    </x-sigem.card>

    <div class="flex flex-wrap justify-end gap-2">
        <x-sigem.button href="{{ route('protocolo.index') }}">Cancelar</x-sigem.button>
        <x-sigem.button type="submit" variant="primary">Autuar e enviar</x-sigem.button>
    </div>
</form>
@endsection
