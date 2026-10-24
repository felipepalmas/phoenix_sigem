{{--
 * Componente da aplicação Phoenix - SIGEM (Zero Papel)
 * Visualização e Motor de Tramitação do Processo Digital
 *
 * @license AGPL-3.0
 --}}

@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
            <a href="{{ route('protocolo.index') }}" class="hover:underline">Protocolo Digital</a>
            <span>/</span>
            <span class="font-semibold text-gray-700 dark:text-gray-300">Processo nº {{ $processo->numero }}</span>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Protocolo {{ $processo->numero }}</h1>
            @php 
                $sBadge = $processo->status_badge; 
                $pBadge = $processo->prioridade_badge; 
            @endphp
            <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $sBadge['bg'] }} {{ $sBadge['text'] }}">
                {{ $sBadge['label'] }}
            </span>
            <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $pBadge['bg'] }} {{ $pBadge['text'] }}">
                Prioridade: {{ $pBadge['label'] }}
            </span>
        </div>
    </div>

    <!-- Barra de Ações Rápidas do Servidor -->
    <div class="flex flex-wrap items-center gap-2">
        @if($processo->status === 'tramitando')
            <button type="button" onclick="abrirModalTramitar()" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                Tramitar / Encaminhar
            </button>
            <button type="button" onclick="document.getElementById('formReceber').submit()" class="inline-flex items-center gap-2 rounded-lg bg-slate-700 px-3.5 py-2.5 text-sm font-medium text-white hover:bg-slate-800 transition" title="Registrar recebimento pelo servidor atual">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Receber
            </button>
            <button type="button" onclick="abrirModalConcluir()" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Decisão / Concluir
            </button>
        @else
            <span class="text-sm font-medium text-gray-500 italic">Processo finalizado em {{ $processo->data_conclusao ? $processo->data_conclusao->format('d/m/Y H:i') : '—' }}</span>
        @endif
        <button type="button" onclick="abrirModalAnexo()" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
            Anexar Peça
        </button>
    </div>
</div>

@if(session('success'))
<div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-sm font-medium text-green-800 dark:border-green-800 dark:bg-green-900/30 dark:text-green-300">
    {{ session('success') }}
</div>
@endif

<!-- Informações Chave do Processo -->
<div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
    <!-- Coluna 1 & 2: Detalhes e Assunto -->
    <div class="lg:col-span-2 space-y-6">
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
            <h2 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Assunto & Requerimento</h2>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-3">{{ $processo->assunto }}</h3>
            @if($processo->descricao)
            <div class="rounded-lg bg-gray-50 p-4 text-sm text-gray-700 dark:bg-gray-900/50 dark:text-gray-300 border border-gray-100 dark:border-gray-800">
                {!! nl2br(e($processo->descricao)) !!}
            </div>
            @endif

            <div class="mt-5 grid grid-cols-2 gap-4 border-t border-gray-100 pt-4 dark:border-gray-700 sm:grid-cols-4 text-xs">
                <div>
                    <span class="text-gray-400 block mb-0.5">Autuado em:</span>
                    <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $processo->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block mb-0.5">Prazo de Resposta:</span>
                    <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $processo->prazo_dias ? $processo->prazo_dias . ' dias' : 'Não estipulado' }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block mb-0.5">Data Limite:</span>
                    <span class="font-semibold {{ $processo->data_limite && $processo->data_limite->isPast() ? 'text-red-600' : 'text-gray-800 dark:text-gray-200' }}">
                        {{ $processo->data_limite ? $processo->data_limite->format('d/m/Y') : '—' }}
                    </span>
                </div>
                <div>
                    <span class="text-gray-400 block mb-0.5">Nível de Sigilo:</span>
                    <span class="font-semibold uppercase text-gray-800 dark:text-gray-200">{{ $processo->sigilo }}</span>
                </div>
            </div>
        </div>

        <!-- Timeline do Motor de Tramitação / Despachos -->
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
            <h2 class="text-base font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                <svg class="h-5 w-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Histórico de Tramitação & Despachos ({{ $processo->tramitacoes->count() }})
            </h2>

            <div class="relative pl-6 space-y-8 before:absolute before:left-2.5 before:top-3 before:bottom-3 before:w-0.5 before:bg-gray-200 dark:before:bg-gray-700">
                @foreach($processo->tramitacoes as $tramitacao)
                <div class="relative">
                    <!-- Marcador da Timeline -->
                    <div class="absolute -left-6 top-1 h-5 w-5 rounded-full border-2 border-white bg-blue-600 dark:border-gray-800 flex items-center justify-center">
                        <span class="h-1.5 w-1.5 rounded-full bg-white"></span>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-4 dark:border-gray-700 dark:bg-gray-900/30">
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                            <div class="flex items-center gap-2 text-xs font-semibold text-gray-800 dark:text-gray-200">
                                <span class="rounded bg-blue-100 px-2 py-0.5 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                    {{ $tramitacao->unidadeOrigem ? $tramitacao->unidadeOrigem->sigla ?? $tramitacao->unidadeOrigem->nome : 'Origem' }}
                                </span>
                                <svg class="h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                <span class="rounded bg-emerald-100 px-2 py-0.5 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    {{ $tramitacao->unidadeDestino ? $tramitacao->unidadeDestino->sigla ?? $tramitacao->unidadeDestino->nome : 'Destino' }}
                                </span>
                            </div>
                            <span class="text-xs text-gray-400">{{ $tramitacao->data_envio->format('d/m/Y H:i') }}</span>
                        </div>

                        <div class="mb-3 text-sm text-gray-700 dark:text-gray-300">
                            {!! nl2br(e($tramitacao->despacho)) !!}
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-gray-200/60 dark:border-gray-700/60 text-xs text-gray-500">
                            <div>
                                Despachado por: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $tramitacao->usuarioEnvio ? $tramitacao->usuarioEnvio->name : 'Sistema' }}</span>
                            </div>
                            @if($tramitacao->parecer_tipo)
                            <div>
                                Parecer: <span class="font-bold uppercase text-brand-600">{{ $tramitacao->parecer_tipo }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Coluna 3: Cidadão & Documentos -->
    <div class="space-y-6">
        <!-- Card Cidadão Interessado -->
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
            <h2 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Cidadão / Contribuinte Interessado</h2>
            @if($processo->interessado)
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-100 text-brand-700 font-bold">
                    {{ substr($processo->interessado->nome, 0, 2) }}
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-gray-900 dark:text-white truncate">{{ $processo->interessado->nome }}</h3>
                    <p class="text-xs text-gray-500">{{ $processo->interessado->documento_principal ?? 'Sem CPF/CNPJ' }}</p>
                </div>
            </div>
            <div class="mt-4 border-t border-gray-100 pt-3 text-xs space-y-1.5 text-gray-600 dark:border-gray-700 dark:text-gray-300">
                <div>E-mail: {{ $processo->interessado->email ?? 'Não informado' }}</div>
                <div>Telefone: {{ $processo->interessado->telefone ?? $processo->interessado->celular ?? 'Não informado' }}</div>
            </div>
            <div class="mt-4">
                <a href="{{ route('clientes.show', $processo->interessado) }}" target="_blank" class="text-xs font-semibold text-brand-600 hover:underline">
                    Ver ficha cadastral completa &rarr;
                </a>
            </div>
            @else
            <p class="text-sm text-gray-500">Nenhum interessado vinculado.</p>
            @endif
        </div>

        <!-- Card Unidade e Servidor Atual -->
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
            <h2 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Localização Atual do Processo</h2>
            <div class="rounded-lg bg-blue-50/60 p-3.5 border border-blue-100 dark:border-blue-900/40 dark:bg-blue-900/20">
                <div class="text-xs font-semibold text-blue-900 dark:text-blue-300 mb-1">Unidade / Secretaria:</div>
                <div class="text-sm font-bold text-blue-700 dark:text-blue-400">
                    {{ $processo->unidadeAtual ? $processo->unidadeAtual->nome_completo : 'Não definida' }}
                </div>
                <div class="mt-2 text-xs text-blue-800/80 dark:text-blue-300/80">
                    Servidor responsável: <span class="font-semibold">{{ $processo->usuarioResponsavel ? $processo->usuarioResponsavel->name : 'Aguardando atribuição' }}</span>
                </div>
            </div>
        </div>

        <!-- Card Documentos & Peças (Zero Papel com SHA-256) -->
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-800">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xs font-bold uppercase tracking-wider text-gray-400">Peças e Anexos Digitais</h2>
                <button type="button" onclick="abrirModalAnexo()" class="text-xs font-semibold text-brand-600 hover:underline">+ Anexar Peça</button>
            </div>

            <div class="space-y-3">
                @forelse($processo->documentos as $doc)
                <div class="rounded-lg border border-gray-200 bg-white p-3 text-xs dark:border-gray-700 dark:bg-gray-900/40">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <h4 class="font-bold text-gray-800 dark:text-gray-100 truncate" title="{{ $doc->titulo }}">{{ $doc->titulo }}</h4>
                            <p class="text-gray-400 text-[11px] truncate">{{ $doc->nome_original }} ({{ $doc->tamanho_formatado }})</p>
                        </div>
                        <a href="{{ route('protocolo.documentos.download', [$processo, $doc]) }}" class="shrink-0 rounded bg-gray-100 p-1.5 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300" title="Baixar Arquivo">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        </a>
                    </div>
                    @if($doc->hash_sha256)
                    <div class="mt-2 rounded bg-gray-50 px-2 py-1 font-mono text-[10px] text-gray-500 dark:bg-gray-800 truncate" title="Hash SHA-256: {{ $doc->hash_sha256 }}">
                        SHA256: {{ substr($doc->hash_sha256, 0, 16) }}...
                    </div>
                    @endif
                </div>
                @empty
                <p class="text-xs text-gray-400 italic">Nenhum documento anexado ainda.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Form Oculto para Receber Processo -->
<form id="formReceber" action="{{ route('protocolo.receber', $processo) }}" method="POST" class="hidden">
    @csrf
</form>

<!-- Modal: Tramitar / Encaminhar Processo -->
<div id="modalTramitar" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-2xl dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Tramitar / Encaminhar Processo</h3>
        <p class="text-xs text-gray-500 mb-4">Transfira o processo para outra Secretaria ou Setor com despacho oficial.</p>

        <form action="{{ route('protocolo.tramitar', $processo) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Unidade de Destino <span class="text-red-500">*</span></label>
                <select name="unidade_destino_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    <option value="">-- Selecione a Secretaria / Setor --</option>
                    @foreach($unidades as $u)
                        <option value="{{ $u->id }}">{{ $u->nome_completo }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de Parecer / Trâmite</label>
                <select name="parecer_tipo" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    <option value="informativo">Informativo (Encaminhamento de Rotina)</option>
                    <option value="favoravel">Parecer Técnico Favorável</option>
                    <option value="desfavoravel">Parecer Técnico Desfavorável</option>
                    <option value="diligencia">Solicitação de Diligência / Informações</option>
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Despacho / Parecer do Servidor <span class="text-red-500">*</span></label>
                <textarea name="despacho" rows="4" required placeholder="Digite o despacho que acompanhará o processo..." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="fecharModalTramitar()" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300">Cancelar</button>
                <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-bold text-white hover:bg-blue-700">Confirmar Tramitação</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Decisão / Conclusão -->
<div id="modalConcluir" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-2xl dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Decisão / Conclusão do Processo</h3>
        <p class="text-xs text-gray-500 mb-4">Registre a decisão final com fé pública para deferir, indeferir ou arquivar os autos.</p>

        <form action="{{ route('protocolo.concluir', $processo) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Decisão Final <span class="text-red-500">*</span></label>
                <select name="status" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    <option value="deferido">Deferido (Solicitação Aprovada / Concedida)</option>
                    <option value="indeferido">Indeferido (Solicitação Negada / Recusada)</option>
                    <option value="arquivado">Arquivado (Finalizado / Cumprido)</option>
                    <option value="suspenso">Suspenso (Aguardando documentação do cidadão)</option>
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Despacho Decisório / Motivação <span class="text-red-500">*</span></label>
                <textarea name="despacho_final" rows="4" required placeholder="Fundamentação e despacho conclusivo..." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="fecharModalConcluir()" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300">Cancelar</button>
                <button type="submit" class="rounded-lg bg-emerald-600 px-5 py-2 text-sm font-bold text-white hover:bg-emerald-700">Registrar Decisão</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Anexar Peça -->
<div id="modalAnexo" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-2xl dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Anexar Nova Peça Digital</h3>
        <p class="text-xs text-gray-500 mb-4">Adicione pareceres, certidões, ofícios ou novos anexos ao processo com cálculo automático de Hash SHA-256.</p>

        <form action="{{ route('protocolo.documentos.store', $processo) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Título da Peça <span class="text-red-500">*</span></label>
                <input type="text" name="titulo" required placeholder="Ex: Parecer Técnico de Engenharia, Laudo de Vistoria..." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de Documento</label>
                <select name="tipo_documento" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    <option value="parecer">Parecer Técnico</option>
                    <option value="despacho">Despacho Avulso</option>
                    <option value="oficio">Ofício</option>
                    <option value="certidao">Certidão / Atestado</option>
                    <option value="anexo">Anexo Geral</option>
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Arquivo (PDF, Imagem, etc.) <span class="text-red-500">*</span></label>
                <input type="file" name="arquivo" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="fecharModalAnexo()" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300">Cancelar</button>
                <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2 text-sm font-bold text-white hover:bg-brand-700">Anexar Peça</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalTramitar() {
    var m = document.getElementById('modalTramitar');
    m.classList.remove('hidden');
    m.classList.add('flex');
}
function fecharModalTramitar() {
    var m = document.getElementById('modalTramitar');
    m.classList.remove('flex');
    m.classList.add('hidden');
}

function abrirModalConcluir() {
    var m = document.getElementById('modalConcluir');
    m.classList.remove('hidden');
    m.classList.add('flex');
}
function fecharModalConcluir() {
    var m = document.getElementById('modalConcluir');
    m.classList.remove('flex');
    m.classList.add('hidden');
}

function abrirModalAnexo() {
    var m = document.getElementById('modalAnexo');
    m.classList.remove('hidden');
    m.classList.add('flex');
}
function fecharModalAnexo() {
    var m = document.getElementById('modalAnexo');
    m.classList.remove('flex');
    m.classList.add('hidden');
}
</script>
@endsection
