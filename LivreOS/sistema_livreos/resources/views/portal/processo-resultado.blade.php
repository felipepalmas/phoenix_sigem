{{--
 * Componente da aplicação Phoenix - SIGEM
 * Visualização Pública da Tramitação de Processo Digital (Conforme LGPD & LAI)
 *
 * @license AGPL-3.0
 --}}

@extends('layouts.portal')

@section('content')
<div class="space-y-6">

    <!-- Breadcrumb de Navegação -->
    <nav class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
        <a href="{{ route('portal.index') }}" class="hover:text-brand-600 transition">Portal do Cidadão</a>
        <span>/</span>
        <span class="text-gray-900 dark:text-white font-semibold">Consulta de Processo</span>
    </nav>

    @if(isset($erro) && $erro)
        <!-- Estado de Não Encontrado -->
        <div class="rounded-3xl border border-red-200 bg-white p-12 text-center shadow-theme-sm dark:border-red-900/40 dark:bg-gray-800">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400 mb-4">
                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h2 class="text-xl font-extrabold text-gray-900 dark:text-white">Processo Não Localizado</h2>
            <p class="mt-2 text-xs text-gray-600 dark:text-gray-400 max-w-md mx-auto leading-relaxed">
                {{ $erro }}
            </p>

            <form action="{{ route('portal.processos') }}" method="GET" class="mt-6 flex max-w-md mx-auto gap-2">
                <input type="text" name="numero" placeholder="Digite outro número de protocolo..." required
                    class="w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-2.5 text-xs text-gray-900 focus:border-brand-500 focus:bg-white dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                <button type="submit" class="rounded-xl bg-brand-600 px-5 py-2.5 text-xs font-bold text-white hover:bg-brand-700">
                    Buscar
                </button>
            </form>

            <div class="mt-6">
                <a href="{{ route('portal.index') }}" class="text-xs font-bold text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                    ← Voltar à Página Inicial
                </a>
            </div>
        </div>

    @elseif(isset($isSigiloso) && $isSigiloso)
        <!-- Processo Sigiloso / Restrito -->
        <div class="rounded-3xl border border-amber-300 bg-amber-50 p-8 shadow-theme-sm dark:border-amber-700/60 dark:bg-amber-900/20">
            <div class="flex items-start gap-4">
                <div class="rounded-2xl bg-amber-100 p-3 text-amber-800 dark:bg-amber-800/40 dark:text-amber-300">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-amber-900 dark:text-amber-200">Processo com Restrição de Acesso (Sigiloso)</h3>
                    <p class="mt-1 text-xs text-amber-800 dark:text-amber-300 leading-relaxed">
                        O processo <strong>{{ $processo->numero }}</strong> está classificado com grau de sigilo nos termos do art. 23 da Lei de Acesso à Informação (Lei 12.527/2011) ou contém dados sensíveis protegidos pela LGPD (Lei 13.709/2018).
                    </p>
                    <p class="mt-2 text-xs text-amber-900 dark:text-amber-200">
                        Para ter vista dos autos, o interessado deve comparecer pessoalmente ao Protocolo Geral da Prefeitura portando documento oficial de identificação com foto.
                    </p>
                    <div class="mt-4">
                        <a href="{{ route('portal.index') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-amber-800 px-4 py-2 text-xs font-bold text-white hover:bg-amber-900 transition">
                            ← Nova Consulta
                        </a>
                    </div>
                </div>
            </div>
        </div>

    @else
        <!-- Processo Público Localizado -->
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800 sm:p-8">

            <!-- Cabeçalho do Processo -->
            <div class="flex flex-col gap-4 border-b border-gray-100 pb-6 dark:border-gray-700 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="inline-flex items-center rounded-lg bg-blue-100 px-3 py-1 font-mono text-base font-black text-blue-900 dark:bg-blue-900/50 dark:text-blue-300">
                            {{ $processo->numero }}
                        </span>
                        @php $sb = $processo->status_badge; @endphp
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ $sb['bg'] }} {{ $sb['text'] }}">
                            {{ $sb['label'] }}
                        </span>
                    </div>
                    <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white">
                        {{ $processo->assunto }}
                    </h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Autuado em: <strong>{{ $processo->created_at->format('d/m/Y \à\s H:i') }}</strong>
                        @if($processo->unidadeAtual)
                            • Custódia Atual: <strong class="text-gray-900 dark:text-white">{{ $processo->unidadeAtual->sigla }} - {{ $processo->unidadeAtual->nome }}</strong>
                        @endif
                    </p>
                </div>

                <!-- Ações do Munícipe (Imprimir / Nova Consulta) -->
                <div class="flex flex-wrap items-center gap-2.5">
                    <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-xs font-bold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition">
                        <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Imprimir Extrato
                    </button>
                    <a href="{{ route('portal.index') }}" class="rounded-xl bg-brand-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-brand-700 transition">
                        Nova Consulta
                    </a>
                </div>
            </div>

            <!-- Dados do Requerente Protegidos pela LGPD -->
            <div class="mt-6 rounded-2xl bg-gray-50 p-4 dark:bg-gray-700/30">
                <div class="flex items-center gap-2 mb-2 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    <svg class="h-4 w-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Requerente / Interessado (Proteção LGPD — Lei 13.709/2018)
                </div>
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 text-xs">
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Nome:</span>
                        <strong class="text-gray-900 dark:text-white font-mono">{{ $dadosRequerente['nome'] }}</strong>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">CPF:</span>
                        <strong class="text-gray-900 dark:text-white font-mono">{{ $dadosRequerente['cpf'] }}</strong>
                    </div>
                </div>
            </div>

            <!-- Descrição Oficial do Requerimento -->
            <div class="mt-6">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                    Objeto do Requerimento
                </h3>
                <p class="text-xs text-gray-700 dark:text-gray-300 leading-relaxed bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                    {{ $processo->descricao }}
                </p>
            </div>

            <!-- Linha do Tempo da Tramitação (Histórico Oficial) -->
            <div class="mt-8">
                <h3 class="text-base font-extrabold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="h-5 w-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Histórico Oficial de Tramitação Eletrônica
                </h3>

                @if($processo->tramitacoes->isEmpty())
                    <p class="text-xs text-gray-500 dark:text-gray-400 italic">O processo aguarda primeira tramitação na unidade de autuação.</p>
                @else
                    <div class="relative border-l-2 border-brand-200 dark:border-brand-900 ml-4 space-y-6 pb-2">
                        @foreach($processo->tramitacoes as $tramite)
                            <div class="relative pl-6">
                                <!-- Ponto da Linha do Tempo -->
                                <div class="absolute -left-2 top-1 h-4 w-4 rounded-full border-2 border-white bg-brand-600 dark:border-gray-800"></div>

                                <div class="rounded-2xl border border-gray-200 bg-gray-50/70 p-4 dark:border-gray-700 dark:bg-gray-700/30">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 mb-2">
                                        <div class="flex items-center gap-2">
                                            <span class="rounded bg-brand-100 px-2 py-0.5 text-[10px] font-black uppercase text-brand-800 dark:bg-brand-900/50 dark:text-brand-300">
                                                {{ $tramite->unidadeOrigem->sigla ?? 'Origem' }} → {{ $tramite->unidadeDestino->sigla ?? 'Destino' }}
                                            </span>
                                            <span class="text-xs font-bold text-gray-900 dark:text-white">
                                                Parecer {{ ucfirst($tramite->parecer_tipo ?? 'Despacho') }}
                                            </span>
                                        </div>
                                        <span class="text-[11px] text-gray-500 dark:text-gray-400">
                                            {{ $tramite->data_envio ? $tramite->data_envio->format('d/m/Y H:i') : $tramite->created_at->format('d/m/Y H:i') }}
                                        </span>
                                    </div>

                                    <p class="text-xs text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">
                                        {{ $tramite->despacho }}
                                    </p>

                                    @if($tramite->status === 'recebido' || $tramite->data_recebimento)
                                        <div class="mt-2.5 pt-2 border-t border-gray-200 dark:border-gray-600 flex items-center gap-1.5 text-[11px] text-emerald-700 dark:text-emerald-400 font-semibold">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Recebido na unidade de destino em {{ $tramite->data_recebimento ? $tramite->data_recebimento->format('d/m/Y H:i') : '' }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Peças & Documentos Públicos Juntados -->
            @if($processo->documentos->isNotEmpty())
                <div class="mt-8 border-t border-gray-100 pt-6 dark:border-gray-700">
                    <h3 class="text-base font-extrabold text-gray-900 dark:text-white mb-3">
                        Peças Documentais Públicas (Certidões & Anexos)
                    </h3>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @foreach($processo->documentos as $doc)
                            <div class="rounded-xl border border-gray-200 p-3.5 dark:border-gray-700 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="rounded-lg bg-blue-50 p-2 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-bold text-gray-900 dark:text-white">{{ $doc->titulo }}</h4>
                                        <p class="text-[10px] text-gray-400 font-mono mt-0.5 truncate max-w-xs" title="Hash SHA-256: {{ $doc->hash_sha256 }}">
                                            SHA-256: {{ substr($doc->hash_sha256, 0, 16) }}...
                                        </p>
                                    </div>
                                </div>
                                <span class="rounded bg-emerald-50 px-2 py-0.5 font-mono text-[10px] font-bold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                                    {{ $doc->tamanho_formatado }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    @endif

</div>
@endsection
