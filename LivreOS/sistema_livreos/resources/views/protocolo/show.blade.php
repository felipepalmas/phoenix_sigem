{{--
 * Componente da aplicação Phoenix - SIGEM (Zero Papel)
 * Protocolo — detalhe do processo com ação contextual (padrão SIGEM Clean)
 *
 * @license AGPL-3.0
 --}}
@extends('layouts.app')

@section('content')
@php
    $encerrado = $processo->estaEncerrado();
    $podeTramitar = $podeAgir && $processo->podeSerTramitado() && !$aguardaRecebimento;
    $podeConcluir = $podeAgir && $processo->podeSerConcluido() && !$aguardaRecebimento;
    $painelInicial = old('_painel', '');
    $dias = $processo->dias_para_prazo;
@endphp

<div x-data="{ painel: @js($painelInicial) }">
<x-sigem.page-header :title="'Processo ' . $processo->numero" :subtitle="$processo->assunto"
    :breadcrumbs="['Protocolo' => route('protocolo.index'), $processo->numero => null]">
    <x-slot:badges>
        <x-sigem.status :tom="$processo->status_tom">{{ $processo->status_rotulo }}</x-sigem.status>
        @if($processo->prioridade === 'urgente')<x-sigem.status tom="danger">Urgente</x-sigem.status>@endif
        @if($processo->sigilo !== 'publico')<x-sigem.status tom="neutral">{{ $processo->sigilo_rotulo }}</x-sigem.status>@endif
    </x-slot:badges>
    <x-slot:actions>
        @if($aguardaRecebimento && $podeAgir)
            <form method="POST" action="{{ route('protocolo.receber', $processo) }}">
                @csrf
                <x-sigem.button type="submit" variant="primary">Receber na {{ $processo->unidadeAtual?->sigla ?: 'unidade' }}</x-sigem.button>
            </form>
        @endif
        @if($podeTramitar)
            <x-sigem.button variant="primary" @click="painel = painel === 'tramitar' ? '' : 'tramitar'" ::aria-expanded="painel === 'tramitar'">Tramitar</x-sigem.button>
        @endif
        @if($podeConcluir)
            <x-sigem.button @click="painel = painel === 'concluir' ? '' : 'concluir'" ::aria-expanded="painel === 'concluir'">Registrar decisão</x-sigem.button>
        @endif
        @if($podeAnexar)
            <x-sigem.button @click="painel = painel === 'anexar' ? '' : 'anexar'" ::aria-expanded="painel === 'anexar'">Anexar documento</x-sigem.button>
        @endif
    </x-slot:actions>
</x-sigem.page-header>

<x-sigem.flash />

@if($aguardaRecebimento && !$encerrado)
    <div class="sigem-alert sigem-alert-info mb-4" role="status">
        <span>
            Este processo foi enviado à <strong>{{ $processo->unidadeAtual?->nome }}</strong>
            {{ optional($pendente?->data_envio)->diffForHumans() }}{{ $pendente?->unidadeOrigem ? ', vindo da ' . ($pendente->unidadeOrigem->sigla ?: $pendente->unidadeOrigem->nome) : '' }}.
            @if($podeAgir)
                Receba-o para poder despachar, tramitar ou decidir.
            @else
                Somente servidores lotados nessa secretaria podem recebê-lo.
            @endif
        </span>
    </div>
@elseif(!$encerrado && !$podeAgir)
    <div class="sigem-alert sigem-alert-info mb-4" role="status">
        <span>O processo está na <strong>{{ $processo->unidadeAtual?->nome }}</strong>. Você pode consultá-lo, mas só servidores lotados nela podem movimentá-lo.</span>
    </div>
@endif

{{-- Painel: tramitar --}}
@if($podeTramitar)
<form x-show="painel === 'tramitar'" x-cloak method="POST" action="{{ route('protocolo.tramitar', $processo) }}" class="sigem-card mb-5 border-sigem-primary">
    @csrf
    <input type="hidden" name="_painel" value="tramitar">
    <div class="sigem-card-header"><h2 class="sigem-card-title">Tramitar processo</h2></div>
    <div class="sigem-card-body flex flex-col gap-4">
        <div class="grid gap-4 md:grid-cols-2">
            <x-sigem.field label="Enviar para" for="unidade_destino_id" required>
                <select id="unidade_destino_id" name="unidade_destino_id" required class="sigem-input">
                    <option value="">Selecione a secretaria</option>
                    @foreach($unidades as $u)
                        <option value="{{ $u->id }}" @selected((string) old('unidade_destino_id') === (string) $u->id)>{{ $u->sigla ? $u->sigla . ' — ' : '' }}{{ $u->nome }}</option>
                    @endforeach
                </select>
            </x-sigem.field>
            <x-sigem.field label="Tipo de manifestação" for="parecer_tipo" required>
                <select id="parecer_tipo" name="parecer_tipo" required class="sigem-input">
                    @foreach(['informativo' => 'Informativo (encaminhamento)', 'favoravel' => 'Favorável', 'desfavoravel' => 'Desfavorável', 'diligencia' => 'Diligência (pedir complemento)'] as $v => $r)
                        <option value="{{ $v }}" @selected(old('parecer_tipo', 'informativo') === $v)>{{ $r }}</option>
                    @endforeach
                </select>
            </x-sigem.field>
        </div>
        <x-sigem.field label="Despacho" for="despacho" required help="Fica no histórico do processo{{ $processo->sigilo === 'publico' ? ' e é visível ao interessado na consulta pública' : '' }}.">
            <textarea id="despacho" name="despacho" rows="4" required minlength="5" class="sigem-input" placeholder="Escreva o despacho que acompanha o envio">{{ old('despacho') }}</textarea>
        </x-sigem.field>
        <div class="flex justify-end gap-2">
            <x-sigem.button @click="painel = ''">Cancelar</x-sigem.button>
            <x-sigem.button type="submit" variant="primary">Enviar processo</x-sigem.button>
        </div>
    </div>
</form>
@endif

{{-- Painel: decisão --}}
@if($podeConcluir)
<form x-show="painel === 'concluir'" x-cloak method="POST" action="{{ route('protocolo.concluir', $processo) }}" class="sigem-card mb-5 border-sigem-primary"
    onsubmit="return confirm('Registrar a decisão encerra o processo (exceto suspensão). Confirmar?');">
    @csrf
    <input type="hidden" name="_painel" value="concluir">
    <div class="sigem-card-header"><h2 class="sigem-card-title">Registrar decisão</h2></div>
    <div class="sigem-card-body flex flex-col gap-4">
        <x-sigem.field label="Decisão" for="status_decisao" required class="md:max-w-sm">
            <select id="status_decisao" name="status" required class="sigem-input">
                @foreach(['deferido' => 'Deferido', 'indeferido' => 'Indeferido', 'arquivado' => 'Arquivado', 'suspenso' => 'Suspenso (aguarda diligência)'] as $v => $r)
                    <option value="{{ $v }}" @selected(old('status') === $v)>{{ $r }}</option>
                @endforeach
            </select>
        </x-sigem.field>
        <x-sigem.field label="Fundamentação" for="despacho_final" required>
            <textarea id="despacho_final" name="despacho_final" rows="4" required minlength="5" class="sigem-input" placeholder="Motivação da decisão">{{ old('despacho_final') }}</textarea>
        </x-sigem.field>
        <div class="flex justify-end gap-2">
            <x-sigem.button @click="painel = ''">Cancelar</x-sigem.button>
            <x-sigem.button type="submit" variant="primary">Registrar decisão</x-sigem.button>
        </div>
    </div>
</form>
@endif

{{-- Painel: anexar --}}
@if($podeAnexar)
<form x-show="painel === 'anexar'" x-cloak method="POST" action="{{ route('protocolo.documentos.store', $processo) }}" enctype="multipart/form-data" class="sigem-card mb-5 border-sigem-primary">
    @csrf
    <input type="hidden" name="_painel" value="anexar">
    <div class="sigem-card-header"><h2 class="sigem-card-title">Anexar documento</h2></div>
    <div class="sigem-card-body flex flex-col gap-4">
        <div class="grid gap-4 md:grid-cols-2">
            <x-sigem.field label="Título" for="titulo" required>
                <input id="titulo" name="titulo" type="text" required maxlength="255" value="{{ old('titulo') }}" class="sigem-input" placeholder="Ex.: Parecer técnico de engenharia">
            </x-sigem.field>
            <x-sigem.field label="Tipo" for="tipo_documento" required>
                <select id="tipo_documento" name="tipo_documento" class="sigem-input">
                    @foreach(['parecer' => 'Parecer', 'despacho' => 'Despacho', 'oficio' => 'Ofício', 'certidao' => 'Certidão', 'anexo' => 'Anexo'] as $v => $r)
                        <option value="{{ $v }}" @selected(old('tipo_documento', 'anexo') === $v)>{{ $r }}</option>
                    @endforeach
                </select>
            </x-sigem.field>
        </div>
        <x-sigem.field label="Arquivo" for="arquivo" required help="PDF, imagem, ODT/DOCX, planilha ou texto, até 20 MB. O sistema calcula o hash SHA-256 para conferência de autenticidade.">
            <input id="arquivo" name="arquivo" type="file" required class="sigem-input h-auto py-1.5" accept=".pdf,.png,.jpg,.jpeg,.webp,.odt,.docx,.doc,.txt,.xls,.xlsx,.csv">
        </x-sigem.field>
        <div class="flex justify-end gap-2">
            <x-sigem.button @click="painel = ''">Cancelar</x-sigem.button>
            <x-sigem.button type="submit" variant="primary">Anexar</x-sigem.button>
        </div>
    </div>
</form>
@endif

<div class="flex flex-wrap items-start gap-5">
    <div class="flex min-w-0 flex-[2_1_480px] flex-col gap-5">
        <x-sigem.card title="Requerimento" id="t-req">
            @if($processo->descricao)
                <p class="m-0 whitespace-pre-line">{{ $processo->descricao }}</p>
            @else
                <p class="sigem-muted m-0">Sem descrição complementar.</p>
            @endif
            <dl class="sigem-dl mt-4 border-t border-gray-100 pt-4 dark:border-gray-800">
                <div><dt>Autuado em</dt><dd>{{ $processo->created_at->format('d/m/Y H:i') }}</dd></div>
                <div><dt>Prazo</dt><dd>
                    @if($processo->data_limite)
                        {{ $processo->data_limite->format('d/m/Y') }}
                        @if($dias !== null)
                            <span class="font-normal {{ $dias < 0 ? 'text-sigem-danger' : ($dias <= 3 ? 'text-sigem-warn' : 'text-sigem-muted') }}">· {{ $dias < 0 ? 'vencido' : ($dias === 0 ? 'hoje' : $dias . ($dias === 1 ? ' dia' : ' dias')) }}</span>
                        @endif
                    @else — @endif
                </dd></div>
                <div><dt>Acesso</dt><dd>{{ $processo->sigilo_rotulo }}</dd></div>
                <div><dt>Autuado por</dt><dd>{{ $processo->criador?->name ?? '—' }}</dd></div>
                @if($processo->data_conclusao)
                    <div><dt>Encerrado em</dt><dd>{{ $processo->data_conclusao->format('d/m/Y H:i') }}</dd></div>
                @endif
            </dl>
        </x-sigem.card>

        <x-sigem.card title="Histórico" id="t-hist">
            @php $historico = $processo->tramitacoes; @endphp
            @if($historico->isEmpty())
                <p class="sigem-muted m-0">Sem movimentações.</p>
            @else
                <x-sigem.timeline>
                    @foreach($historico as $t)
                        <x-sigem.timeline-item :destaque="$loop->first" :ultimo="$loop->last">
                            <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1">
                                <strong>
                                    @if($t->status === 'concluido')
                                        Decisão em {{ $t->unidadeOrigem?->sigla ?: $t->unidadeOrigem?->nome }}
                                    @elseif($t->unidade_origem_id === $t->unidade_destino_id)
                                        Autuação em {{ $t->unidadeDestino?->sigla ?: $t->unidadeDestino?->nome }}
                                    @else
                                        {{ $t->unidadeOrigem?->sigla ?: $t->unidadeOrigem?->nome }} → {{ $t->unidadeDestino?->sigla ?: $t->unidadeDestino?->nome }}
                                    @endif
                                </strong>
                                <span class="text-[13px] text-sigem-muted dark:text-gray-400">{{ optional($t->data_envio)->format('d/m/Y H:i') }} · {{ $t->usuarioEnvio?->name ?? 'Sistema' }}</span>
                                <span class="rounded border border-sigem-line px-1.5 text-xs text-sigem-muted dark:border-gray-700 dark:text-gray-400">{{ ['favoravel' => 'Favorável', 'desfavoravel' => 'Desfavorável', 'informativo' => 'Informativo', 'diligencia' => 'Diligência'][$t->parecer_tipo] ?? ucfirst($t->parecer_tipo) }}</span>
                            </div>
                            <p class="m-0 mt-1 whitespace-pre-line">{{ $t->despacho }}</p>
                            <div class="mt-1 text-[13px]">
                                @if($t->status === 'enviado')
                                    <span class="text-sigem-primary dark:text-brand-300">Aguardando recebimento</span>
                                @elseif($t->status === 'recebido')
                                    <span class="text-sigem-muted dark:text-gray-400">Recebido{{ $t->usuarioRecebimento ? ' por ' . $t->usuarioRecebimento->name : '' }}{{ $t->data_recebimento ? ' em ' . $t->data_recebimento->format('d/m/Y H:i') : '' }}</span>
                                @endif
                            </div>
                        </x-sigem.timeline-item>
                    @endforeach
                </x-sigem.timeline>
            @endif
        </x-sigem.card>
    </div>

    <aside class="flex min-w-0 flex-[1_1_280px] flex-col gap-5">
        <x-sigem.card title="Onde está" id="t-onde">
            <div class="font-semibold">{{ $processo->unidadeAtual?->sigla ? $processo->unidadeAtual->sigla . ' — ' : '' }}{{ $processo->unidadeAtual?->nome ?? '—' }}</div>
            <div class="sigem-muted">Responsável: {{ $processo->usuarioResponsavel?->name ?? ($aguardaRecebimento ? 'aguardando recebimento' : 'não atribuído') }}</div>
        </x-sigem.card>

        <x-sigem.card title="Interessado" id="t-int">
            @php $int = $processo->interessado; @endphp
            @if($int)
                <div class="font-semibold">{{ $int->nome ?: $int->razao_social }}</div>
                <div class="sigem-muted">{{ $int->cpf ? 'CPF ' . $int->cpf : ($int->cnpj ? 'CNPJ ' . $int->cnpj : 'Documento não informado') }}</div>
                @if(Route::has('clientes.show'))
                    <a href="{{ route('clientes.show', $int) }}" class="sigem-link mt-2 inline-block text-[13px] font-normal">Abrir cadastro</a>
                @endif
            @else
                <p class="sigem-muted m-0">Não informado.</p>
            @endif
        </x-sigem.card>

        <x-sigem.card title="Documentos" id="t-docs">
            <x-slot:actions><span class="text-[13px] text-sigem-muted">{{ $processo->documentos->count() }}</span></x-slot:actions>
            @if($processo->documentos->isEmpty())
                <p class="sigem-muted m-0">Nenhum documento anexado.</p>
            @else
                <ul class="m-0 flex list-none flex-col gap-3 p-0">
                    @foreach($processo->documentos as $doc)
                        <li class="flex gap-2.5">
                            <svg aria-hidden="true" class="mt-0.5 h-[18px] w-[18px] shrink-0 text-sigem-muted" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M7 3h7l5 5v12a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z"/><path d="M14 3v5h5"/></svg>
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('protocolo.documentos.download', [$processo, $doc]) }}" class="sigem-link block truncate font-normal" title="{{ $doc->nome_original }}">{{ $doc->titulo }}</a>
                                <div class="text-xs text-sigem-muted dark:text-gray-400">{{ $doc->created_at->format('d/m/Y') }} · {{ $doc->tamanho_bytes ? number_format($doc->tamanho_bytes / 1024, 0, ',', '.') . ' KB' : '' }}</div>
                                @if($doc->hash_sha256)
                                    <div class="truncate font-mono text-[11px] text-sigem-muted" title="SHA-256: {{ $doc->hash_sha256 }}">SHA-256 {{ substr($doc->hash_sha256, 0, 16) }}…</div>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-sigem.card>

        @if($processo->chave_acesso && $processo->sigilo === 'publico')
            <x-sigem.card title="Consulta pública" id="t-consulta">
                <p class="m-0 text-[13px] text-sigem-muted dark:text-gray-400">Entregue ao interessado para acompanhar pelo Portal do Cidadão:</p>
                <dl class="sigem-dl mt-2">
                    <div><dt>Número</dt><dd class="font-mono">{{ $processo->numero }}</dd></div>
                    <div><dt>Chave de acesso</dt><dd class="font-mono tracking-wider">{{ $processo->chave_acesso }}</dd></div>
                </dl>
            </x-sigem.card>
        @endif
    </aside>
</div>
</div>
@endsection
