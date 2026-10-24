{{--
 * Componente da aplicação Phoenix - SIGEM
 * Portal do Cidadão — validar autenticidade de documento (SHA-256)
 *
 * @license AGPL-3.0
 --}}
@extends('layouts.portal')

@section('content')
<div class="mx-auto max-w-3xl">
    <x-sigem.page-header title="Validar um documento" subtitle="Confira se um documento emitido pela Prefeitura é verdadeiro e não foi alterado." />

    <x-sigem.card class="mb-5">
        <form method="GET" action="{{ route('portal.autenticidade') }}" class="flex flex-col gap-3">
            <x-sigem.field label="Código de verificação (SHA-256)" for="hash" required help="Sequência de 64 letras e números impressa no rodapé do documento.">
                <input id="hash" name="hash" type="text" required value="{{ $termo }}" class="sigem-input font-mono" autocomplete="off" spellcheck="false">
            </x-sigem.field>
            <div><x-sigem.button type="submit" variant="primary">Validar</x-sigem.button></div>
        </form>
    </x-sigem.card>

    @if($resultado)
        @if($resultado['autentico'])
            <div class="sigem-alert sigem-alert-ok mb-4" role="status"><span><strong>Documento autêntico.</strong> O código confere com um documento registrado no sistema da Prefeitura.</span></div>
            <x-sigem.card>
                <dl class="sigem-dl">
                    <div><dt>Documento</dt><dd>{{ $resultado['documento']->titulo }}</dd></div>
                    <div><dt>Registrado em</dt><dd>{{ optional($resultado['data_emissao'])->format('d/m/Y H:i') }}</dd></div>
                    @if(!empty($resultado['processo']))
                        <div><dt>Processo</dt><dd>{{ $resultado['processo']->numero }}</dd></div>
                    @endif
                    <div><dt>Assinatura</dt><dd>{{ $resultado['assinado'] ? 'Assinado por ' . $resultado['assinado_por'] : 'Sem assinatura digital' }}</dd></div>
                </dl>
                @if(!empty($resultado['aviso_sigilo']))
                    <p class="sigem-muted m-0 mt-3">{{ $resultado['aviso_sigilo'] }}</p>
                @endif
                <p class="m-0 mt-3 break-all font-mono text-xs text-sigem-muted">SHA-256: {{ $resultado['hash'] }}</p>
            </x-sigem.card>
        @else
            <div class="sigem-alert sigem-alert-danger" role="alert"><span><strong>Não confere.</strong> {{ $resultado['mensagem'] }}</span></div>
        @endif
    @endif
</div>
@endsection
