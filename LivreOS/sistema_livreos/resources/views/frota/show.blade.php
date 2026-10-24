{{--
 * Componente da aplicação Phoenix - SIGEM
 * Frota — ficha do veículo: viagens, abastecimentos e manutenções (padrão SIGEM Clean)
 *
 * @license AGPL-3.0
 --}}
@extends('layouts.app')

@section('content')
@php
    use App\Models\Veiculo;
    $painelInicial = old('_painel', '');
    $un = $veiculo->unidade_medida;
    $fmtNum = fn ($n, $d = 0) => number_format((float) $n, $d, ',', '.');
    $fmtMoeda = fn ($n) => 'R$ ' . number_format((float) $n, 2, ',', '.');
@endphp

<div x-data="{ painel: @js($painelInicial) }">
<x-sigem.page-header :title="$veiculo->placa . ' — ' . $veiculo->marca . ' ' . $veiculo->modelo"
    :subtitle="$veiculo->tipo_formatado . ' · ' . ($veiculo->unidadeAdministrativa?->nome ?? 'Garagem central')"
    :breadcrumbs="['Frota' => route('frota.index'), $veiculo->placa => null]">
    <x-slot:badges>
        <x-sigem.status :tom="$veiculo->status_tom">{{ $veiculo->status_formatado }}</x-sigem.status>
        @unless($veiculo->ativo)<x-sigem.status tom="neutral">Inativo</x-sigem.status>@endunless
    </x-slot:badges>
    <x-slot:actions>
        @if($podeSair)
            <x-sigem.button variant="primary" @click="painel = painel === 'saida' ? '' : 'saida'" ::aria-expanded="painel === 'saida'">Registrar saída</x-sigem.button>
        @elseif($viagemAtiva)
            <x-sigem.button variant="primary" @click="painel = painel === 'retorno-{{ $viagemAtiva->id }}' ? '' : 'retorno-{{ $viagemAtiva->id }}'" ::aria-expanded="painel === 'retorno-{{ $viagemAtiva->id }}'">Registrar retorno</x-sigem.button>
        @endif
        @if($podeAbastecer)
            <x-sigem.button @click="painel = painel === 'abastecimento' ? '' : 'abastecimento'" ::aria-expanded="painel === 'abastecimento'">Lançar abastecimento</x-sigem.button>
        @endif
    </x-slot:actions>
</x-sigem.page-header>

<x-sigem.flash />

@if($viagemAtiva)
    <div class="sigem-alert sigem-alert-info mb-4" role="status">
        <span>
            Em viagem desde {{ $viagemAtiva->data_saida?->format('d/m/Y \à\s H:i') }}
            com {{ $viagemAtiva->motorista?->nome ?? 'motorista não informado' }}, destino <strong>{{ $viagemAtiva->destino }}</strong>
            (viagem {{ $viagemAtiva->numero }}).
        </span>
    </div>
@elseif(in_array($veiculo->status, ['em_manutencao', 'inoperante'], true))
    <div class="sigem-alert sigem-alert-warn mb-4" role="status">
        <span>Veículo {{ mb_strtolower($veiculo->status_formatado) }}: não pode sair até ser liberado.</span>
    </div>
@endif

{{-- Painel: registrar saída --}}
@if($podeSair)
    @include('frota.partials.form-saida', ['veiculo' => $veiculo, 'motoristas' => $motoristas])
@endif

{{-- Painel: registrar retorno --}}
@if($viagemAtiva)
    <section x-show="painel === 'retorno-{{ $viagemAtiva->id }}'" x-cloak class="sigem-card mb-5 border-sigem-primary" aria-labelledby="t-retorno">
        <div class="sigem-card-header">
            <div class="min-w-0 flex-1">
                <h2 class="sigem-card-title" id="t-retorno">Registrar retorno</h2>
                <p class="m-0 mt-0.5 text-[13px] text-sigem-muted dark:text-gray-400">Viagem {{ $viagemAtiva->numero }} · {{ $viagemAtiva->destino }}</p>
            </div>
        </div>
        <div class="sigem-card-body">
            @include('frota.partials.form-retorno', ['viagem' => $viagemAtiva->setRelation('veiculo', $veiculo)])
        </div>
    </section>
@endif

{{-- Painel: abastecimento --}}
@if($podeAbastecer)
<form x-show="painel === 'abastecimento'" x-cloak method="POST" action="{{ route('frota.abastecimentos.store') }}" class="sigem-card mb-5 border-sigem-primary"
    x-data="{ litros: @js((string) old('litros', '')), valorLitro: @js((string) old('valor_litro', '')) }">
    @csrf
    <input type="hidden" name="_painel" value="abastecimento">
    <input type="hidden" name="veiculo_id" value="{{ $veiculo->id }}">
    <div class="sigem-card-header">
        <div class="min-w-0 flex-1">
            <h2 class="sigem-card-title">Lançar abastecimento</h2>
            <p class="m-0 mt-0.5 text-[13px] text-sigem-muted dark:text-gray-400">Confira os dados no cupom fiscal do posto.</p>
        </div>
    </div>
    <div class="sigem-card-body flex flex-col gap-4">
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <x-sigem.field label="Data" for="ab_data" :error="$errors->first('data') ?: null" required>
                <input id="ab_data" name="data" type="date" required max="{{ now()->format('Y-m-d') }}" value="{{ old('data', now()->format('Y-m-d')) }}" class="sigem-input">
            </x-sigem.field>
            <x-sigem.field label="Combustível" for="ab_combustivel" :error="$errors->first('combustivel') ?: null" required>
                <select id="ab_combustivel" name="combustivel" required class="sigem-input">
                    @foreach(Veiculo::COMBUSTIVEIS as $valor => $rotulo)
                        <option value="{{ $valor }}" @selected(old('combustivel', $veiculo->combustivel === 'flex' ? 'gasolina' : $veiculo->combustivel) === $valor)>{{ $rotulo }}</option>
                    @endforeach
                </select>
            </x-sigem.field>
            <x-sigem.field label="Litros" for="ab_litros" :error="$errors->first('litros') ?: null" required
                :help="(float) $veiculo->capacidade_tanque > 0 ? 'Tanque de ' . $fmtNum($veiculo->capacidade_tanque, 0) . ' L.' : 'Capacidade do tanque não cadastrada.'">
                <input id="ab_litros" name="litros" type="number" required step="0.01" min="0.1" @if((float) $veiculo->capacidade_tanque > 0) max="{{ (float) $veiculo->capacidade_tanque }}" @endif
                    x-model="litros" class="sigem-input tabular-nums @error('litros') sigem-input-invalid @enderror">
            </x-sigem.field>
            <x-sigem.field label="Preço por litro (R$)" for="ab_valor_litro" :error="$errors->first('valor_litro') ?: null" required>
                <input id="ab_valor_litro" name="valor_litro" type="number" required step="0.001" min="0.1" x-model="valorLitro" class="sigem-input tabular-nums">
            </x-sigem.field>
            <x-sigem.field label="Hodômetro no abastecimento" for="ab_km" :error="$errors->first('km_veiculo') ?: null" required :help="'Atual: ' . $veiculo->hodometro_formatado . '.'">
                <input id="ab_km" name="km_veiculo" type="number" required step="1" min="{{ (int) $veiculo->km_atual }}" value="{{ old('km_veiculo', $veiculo->km_atual) }}" class="sigem-input tabular-nums @error('km_veiculo') sigem-input-invalid @enderror">
            </x-sigem.field>
            <x-sigem.field label="Posto" for="ab_posto" :error="$errors->first('posto_combustivel') ?: null" required>
                <input id="ab_posto" name="posto_combustivel" type="text" required maxlength="100" value="{{ old('posto_combustivel') }}" placeholder="Nome do posto contratado" class="sigem-input">
            </x-sigem.field>
            <x-sigem.field label="Nº do cupom ou nota fiscal" for="ab_cupom">
                <input id="ab_cupom" name="numero_cupom_fiscal" type="text" maxlength="50" value="{{ old('numero_cupom_fiscal') }}" class="sigem-input">
            </x-sigem.field>
            <x-sigem.field label="Motorista" for="ab_motorista">
                <select id="ab_motorista" name="motorista_id" class="sigem-input">
                    <option value="">Não informado</option>
                    @foreach($motoristas as $m)
                        <option value="{{ $m->id }}" @selected((string) old('motorista_id', $viagemAtiva?->motorista_id) === (string) $m->id)>{{ $m->nome }}</option>
                    @endforeach
                </select>
            </x-sigem.field>
            <x-sigem.field label="Observações" for="ab_obs" class="md:col-span-2 lg:col-span-4">
                <input id="ab_obs" name="observacoes" type="text" value="{{ old('observacoes') }}" class="sigem-input">
            </x-sigem.field>
        </div>
        <div class="flex flex-wrap items-center justify-end gap-2">
            <p class="m-0 mr-auto text-sm" aria-live="polite">
                Total: <strong class="tabular-nums" x-text="(parseFloat(litros) > 0 && parseFloat(valorLitro) > 0) ? (parseFloat(litros) * parseFloat(valorLitro)).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) : '—'">—</strong>
            </p>
            <x-sigem.button @click="painel = ''">Cancelar</x-sigem.button>
            <x-sigem.button type="submit" variant="primary">Lançar abastecimento</x-sigem.button>
        </div>
    </div>
</form>
@endif

<div class="flex flex-wrap items-start gap-5">
    <div class="flex min-w-0 flex-[2_1_560px] flex-col gap-5">
        <x-sigem.card :padded="false" title="Viagens" id="t-viagens" :subtitle="$totalViagens > $viagens->count() ? 'Últimas ' . $viagens->count() . ' de ' . $totalViagens : null">
            <x-slot:actions>
                <a href="{{ route('frota.viagens.index', ['recorte' => 'todas', 'busca' => $veiculo->placa]) }}" class="sigem-link text-[13px] font-normal">Ver no diário de bordo</a>
            </x-slot:actions>
            @if($viagens->isEmpty())
                <x-sigem.empty title="Nenhuma viagem registrada" description="As saídas registradas para este veículo aparecem aqui." />
            @else
                <x-sigem.table min-width="620px">
                    <x-slot:head>
                        <th scope="col">Saída</th>
                        <th scope="col">Destino, motorista e finalidade</th>
                        <th scope="col" class="text-right">Percurso</th>
                        <th scope="col">Situação</th>
                    </x-slot:head>
                    @foreach($viagens as $viagem)
                        <tr>
                            <td class="whitespace-nowrap">
                                {{ $viagem->data_saida?->format('d/m/Y H:i') }}
                                <div class="sigem-muted">{{ $viagem->numero }}</div>
                            </td>
                            <td class="max-w-[260px]">
                                <div class="truncate">{{ $viagem->destino }}</div>
                                <div class="truncate sigem-muted" title="{{ $viagem->finalidade }}">{{ $viagem->motorista?->nome ?? 'Motorista não informado' }} — {{ $viagem->finalidade }}</div>
                                @if($viagem->ocorrencias)<div class="truncate text-xs font-semibold text-sigem-warn dark:text-amber-300" title="{{ $viagem->ocorrencias }}">Ocorrência: {{ $viagem->ocorrencias }}</div>@endif
                            </td>
                            <td class="whitespace-nowrap text-right tabular-nums">
                                @if($viagem->km_percorrido !== null && $viagem->situacao === 'concluida')
                                    {{ $fmtNum($viagem->km_percorrido) }} {{ $un }}
                                    <div class="sigem-muted">{{ $fmtNum($viagem->km_saida) }} → {{ $fmtNum($viagem->km_retorno) }}</div>
                                @else
                                    <span class="sigem-muted">saiu com {{ $fmtNum($viagem->km_saida) }} {{ $un }}</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap"><x-sigem.status :tom="$viagem->situacao_tom">{{ $viagem->situacao_formatada }}</x-sigem.status></td>
                        </tr>
                    @endforeach
                </x-sigem.table>
            @endif
        </x-sigem.card>

        <x-sigem.card :padded="false" title="Abastecimentos" id="t-abast" :subtitle="$totalAbastecimentos > $abastecimentos->count() ? 'Últimos ' . $abastecimentos->count() . ' de ' . $totalAbastecimentos : null">
            @if($abastecimentos->isEmpty())
                <x-sigem.empty title="Nenhum abastecimento lançado" description="Lance cada abastecimento com o cupom do posto para acompanhar consumo e gasto.">
                    @if($podeAbastecer)<x-sigem.button @click="painel = 'abastecimento'; window.scrollTo({ top: 0, behavior: 'smooth' })">Lançar abastecimento</x-sigem.button>@endif
                </x-sigem.empty>
            @else
                <x-sigem.table min-width="720px">
                    <x-slot:head>
                        <th scope="col">Data</th>
                        <th scope="col" class="text-right">Litros</th>
                        <th scope="col" class="text-right">Total</th>
                        <th scope="col" class="text-right">Hodômetro</th>
                        <th scope="col" class="text-right">Consumo</th>
                        <th scope="col">Posto</th>
                    </x-slot:head>
                    @foreach($abastecimentos as $ab)
                        <tr>
                            <td class="whitespace-nowrap">
                                {{ $ab->data?->format('d/m/Y') }}
                                <div class="sigem-muted">{{ Veiculo::rotuloCombustivel($ab->combustivel) }}</div>
                            </td>
                            <td class="whitespace-nowrap text-right tabular-nums">
                                {{ $fmtNum($ab->litros, 2) }} L
                                <div class="sigem-muted">{{ $fmtMoeda($ab->valor_litro) }}/L</div>
                            </td>
                            <td class="whitespace-nowrap text-right tabular-nums font-semibold">{{ $fmtMoeda($ab->valor_total) }}</td>
                            <td class="whitespace-nowrap text-right tabular-nums">{{ $fmtNum($ab->km_veiculo) }} {{ $un }}</td>
                            <td class="whitespace-nowrap text-right tabular-nums">{{ $ab->consumo_medio_kml ? $fmtNum($ab->consumo_medio_kml, 2) . ' ' . $un . '/L' : '—' }}</td>
                            <td class="max-w-[200px]">
                                <div class="truncate" title="{{ $ab->posto_combustivel }}">{{ $ab->posto_combustivel }}</div>
                                @if($ab->numero_cupom_fiscal)<div class="sigem-muted">Cupom {{ $ab->numero_cupom_fiscal }}</div>@endif
                            </td>
                        </tr>
                    @endforeach
                </x-sigem.table>
            @endif
        </x-sigem.card>

        <x-sigem.card :padded="false" title="Manutenções" id="t-manut" :subtitle="$totalManutencoes > $manutencoes->count() ? 'Últimas ' . $manutencoes->count() . ' de ' . $totalManutencoes : null">
            @if($manutencoes->isEmpty())
                <x-sigem.empty title="Nenhuma manutenção registrada" description="Ordens de manutenção preventiva e corretiva deste veículo aparecem aqui." />
            @else
                <x-sigem.table min-width="680px">
                    <x-slot:head>
                        <th scope="col">Ordem</th>
                        <th scope="col">Serviço</th>
                        <th scope="col">Agendada</th>
                        <th scope="col" class="text-right">Valor estimado</th>
                        <th scope="col">Situação</th>
                    </x-slot:head>
                    @foreach($manutencoes as $man)
                        <tr>
                            <td class="whitespace-nowrap">
                                {{ $man->numero }}
                                <div class="sigem-muted">{{ $man->tipo_formatado }}</div>
                            </td>
                            <td class="max-w-[280px]">
                                <div class="truncate" title="{{ $man->descricao_problema }}">{{ $man->descricao_problema ?: '—' }}</div>
                                @if($man->oficina_executora)<div class="truncate sigem-muted">{{ $man->oficina_executora }}</div>@endif
                            </td>
                            <td class="whitespace-nowrap">{{ $man->data_agendamento?->format('d/m/Y') ?? '—' }}</td>
                            <td class="whitespace-nowrap text-right tabular-nums">{{ (float) $man->valor_total_estimado > 0 ? $fmtMoeda($man->valor_total_estimado) : '—' }}</td>
                            <td><x-sigem.status :tom="$man->status_tom">{{ $man->status_formatado }}</x-sigem.status></td>
                        </tr>
                    @endforeach
                </x-sigem.table>
            @endif
        </x-sigem.card>
    </div>

    <aside class="flex min-w-0 flex-[1_1_280px] flex-col gap-5">
        <x-sigem.card title="Dados do veículo" id="t-dados">
            <dl class="sigem-dl">
                <div><dt>{{ $veiculo->usa_horimetro ? 'Horímetro' : 'Hodômetro' }}</dt><dd class="tabular-nums">{{ $veiculo->hodometro_formatado }}</dd></div>
                <div><dt>Ano</dt><dd>{{ $veiculo->ano_fabricacao }}/{{ $veiculo->ano_modelo }}</dd></div>
                <div><dt>Combustível</dt><dd>{{ $veiculo->combustivel_formatado }}</dd></div>
                <div><dt>Tanque</dt><dd>{{ (float) $veiculo->capacidade_tanque > 0 ? $fmtNum($veiculo->capacidade_tanque, 0) . ' L' : '—' }}</dd></div>
                <div><dt>Lugares</dt><dd>{{ $veiculo->capacidade_passageiros ?: '—' }}</dd></div>
                <div><dt>Secretaria</dt><dd>{{ $veiculo->unidadeAdministrativa?->sigla ?: ($veiculo->unidadeAdministrativa?->nome ?? 'Garagem central') }}</dd></div>
                <div><dt>Patrimônio</dt><dd>{{ $veiculo->patrimonio_tombamento ?: '—' }}</dd></div>
                <div><dt>Renavam</dt><dd>{{ $veiculo->renavam ?: '—' }}</dd></div>
                <div class="col-span-full"><dt>Chassi</dt><dd class="break-all font-mono text-[13px]">{{ $veiculo->chassi ?: '—' }}</dd></div>
                @if($veiculo->motoristaResponsavel)
                    <div><dt>Responsável</dt><dd>{{ $veiculo->motoristaResponsavel->name }}</dd></div>
                @endif
            </dl>
        </x-sigem.card>

        @if($veiculo->observacoes)
            <x-sigem.card title="Observações" id="t-obs">
                <p class="m-0 whitespace-pre-line">{{ $veiculo->observacoes }}</p>
            </x-sigem.card>
        @endif
    </aside>
</div>
</div>
@endsection
