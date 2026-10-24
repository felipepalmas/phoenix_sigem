{{--
 * Componente da aplicação Phoenix - SIGEM
 * Ficha Técnica e Dossiê Completo do Veículo Oficial
 *
 * @license AGPL-3.0
 --}}

@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ modalSaida: false, modalAbastecimento: false, modalRetorno: false, viagemSelecionada: null }">

    <!-- Mensagens de Flash -->
    @if(session('success'))
        <div class="rounded-xl border border-emerald-300 bg-emerald-50 p-4 dark:border-emerald-700/60 dark:bg-emerald-900/20">
            <div class="flex items-center gap-3">
                <div class="rounded-lg bg-emerald-100 p-2 text-emerald-800 dark:bg-emerald-800/40 dark:text-emerald-300">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <p class="text-sm font-semibold text-emerald-900 dark:text-emerald-200">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <!-- Breadcrumb e Topbar da Viatura -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <nav class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-1">
                <a href="{{ route('frota.index') }}" class="hover:text-brand-600 transition">Frota Municipal</a>
                <span>/</span>
                <span class="text-gray-900 dark:text-white font-semibold">Ficha Técnica</span>
            </nav>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-black text-gray-900 dark:text-white sm:text-3xl">
                    {{ $veiculo->modelo }}
                </h1>
                <span class="inline-flex items-center rounded-lg border border-gray-300 bg-gray-100 px-3 py-1 font-mono text-base font-black text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-xs">
                    {{ $veiculo->placa }}
                </span>
                {!! $veiculo->status_badge !!}
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                {{ $veiculo->marca }} • {{ $veiculo->ano_fabricacao }}/{{ $veiculo->ano_modelo }} • {{ $veiculo->combustivel }}
                @if($veiculo->unidadeAdministrativa)
                    • Lotação: <span class="font-bold text-gray-900 dark:text-white">{{ $veiculo->unidadeAdministrativa->sigla ?? $veiculo->unidadeAdministrativa->nome }}</span>
                @endif
            </p>
        </div>

        <!-- Botões de Ação Imediata -->
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('frota.index') }}" class="rounded-xl border border-gray-300 bg-white px-3.5 py-2.5 text-xs font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition">
                ← Voltar
            </a>

            @if($veiculo->status === 'disponivel')
                <button type="button" @click="modalSaida = true" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-blue-700 transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                    Registrar Saída (Viagem)
                </button>
            @elseif($veiculo->status === 'em_viagem')
                @php $viagemAtiva = $veiculo->viagens()->where('situacao', 'em_andamento')->latest('data_saida')->first(); @endphp
                @if($viagemAtiva)
                    <button type="button" @click="viagemSelecionada = {{ $viagemAtiva->id }}; modalRetorno = true" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-emerald-700 transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                        Registrar Retorno à Garagem
                    </button>
                @endif
            @endif

            <button type="button" @click="modalAbastecimento = true" class="inline-flex items-center gap-2 rounded-xl border border-amber-300 bg-amber-50 px-4 py-2.5 text-xs font-bold text-amber-800 hover:bg-amber-100 dark:border-amber-700/60 dark:bg-amber-900/30 dark:text-amber-300 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                Lançar Abastecimento
            </button>
        </div>
    </div>

    <!-- Cards de Métricas do Veículo -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Odômetro Atual</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold font-mono text-gray-900 dark:text-white">{{ number_format($veiculo->km_atual, 0, ',', '.') }}</span>
                <span class="text-sm font-semibold text-gray-500">Km</span>
            </div>
            <p class="text-xs text-gray-500 mt-1">Registrado no último diário de bordo</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Consumo Médio</span>
            <div class="mt-2 flex items-baseline gap-2">
                @php
                    $ultimoConsumo = $abastecimentos->firstWhere('consumo_medio_kml', '!=', null);
                @endphp
                <span class="text-3xl font-extrabold font-mono text-emerald-600 dark:text-emerald-400">
                    {{ $ultimoConsumo ? number_format($ultimoConsumo->consumo_medio_kml, 2, ',', '.') : '--' }}
                </span>
                <span class="text-sm font-semibold text-gray-500">Km/L</span>
            </div>
            <p class="text-xs text-gray-500 mt-1">Cálculo bomba a bomba</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total de Viagens</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-blue-600 dark:text-blue-400">{{ $veiculo->viagens()->count() }}</span>
                <span class="text-sm font-semibold text-gray-500">missões</span>
            </div>
            <p class="text-xs text-gray-500 mt-1">Diário de bordo auditável</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Despesa Combustível</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-extrabold text-gray-900 dark:text-white">
                    R$ {{ number_format($veiculo->abastecimentos()->sum('valor_total'), 2, ',', '.') }}
                </span>
            </div>
            <p class="text-xs text-gray-500 mt-1">{{ number_format($veiculo->abastecimentos()->sum('litros'), 1, ',', '.') }} Litros abastecidos</p>
        </div>
    </div>

    <!-- Grade: Informações Técnicas & Histórico -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <!-- Coluna Esquerda: Detalhes Cadastrais -->
        <div class="space-y-6 lg:col-span-1">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800">
                <h3 class="text-base font-bold text-gray-900 dark:text-white border-b border-gray-100 pb-3 dark:border-gray-700 mb-4">
                    Dados Patrimoniais
                </h3>

                <dl class="space-y-3 text-xs">
                    <div class="flex justify-between py-1 border-b border-gray-50 dark:border-gray-700/50">
                        <dt class="font-medium text-gray-500 dark:text-gray-400">Patrimônio / Tombamento:</dt>
                        <dd class="font-bold font-mono text-gray-900 dark:text-white">{{ $veiculo->patrimonio_tombamento ?? 'Não tombado' }}</dd>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-50 dark:border-gray-700/50">
                        <dt class="font-medium text-gray-500 dark:text-gray-400">RENAVAM:</dt>
                        <dd class="font-bold font-mono text-gray-900 dark:text-white">{{ $veiculo->renavam ?? 'Não informado' }}</dd>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-50 dark:border-gray-700/50">
                        <dt class="font-medium text-gray-500 dark:text-gray-400">Chassi:</dt>
                        <dd class="font-bold font-mono text-gray-900 dark:text-white uppercase">{{ $veiculo->chassi ?? 'Não informado' }}</dd>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-50 dark:border-gray-700/50">
                        <dt class="font-medium text-gray-500 dark:text-gray-400">Capacidade Tanque:</dt>
                        <dd class="font-bold text-gray-900 dark:text-white">{{ $veiculo->capacidade_tanque ? $veiculo->capacidade_tanque . ' L' : 'N/I' }}</dd>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-50 dark:border-gray-700/50">
                        <dt class="font-medium text-gray-500 dark:text-gray-400">Lotação Passageiros:</dt>
                        <dd class="font-bold text-gray-900 dark:text-white">{{ $veiculo->capacidade_passageiros ?? 5 }} lugares</dd>
                    </div>
                    <div class="flex justify-between py-1">
                        <dt class="font-medium text-gray-500 dark:text-gray-400">Motorista Fixo / Habitual:</dt>
                        <dd class="font-bold text-gray-900 dark:text-white">{{ $veiculo->motoristaResponsavel ? $veiculo->motoristaResponsavel->user->name : 'Qualquer credenciado' }}</dd>
                    </div>
                </dl>

                @if($veiculo->observacoes)
                    <div class="mt-4 rounded-xl bg-gray-50 p-3 text-xs text-gray-700 dark:bg-gray-700/40 dark:text-gray-300">
                        <span class="font-bold block text-[11px] uppercase tracking-wider text-gray-400 mb-1">Observações da Garagem:</span>
                        {{ $veiculo->observacoes }}
                    </div>
                @endif
            </div>

            <!-- Manutenções e Preventivas -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-700 mb-4">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">
                        Manutenções & Oficina
                    </h3>
                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-bold text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        {{ $manutencoes->count() }}
                    </span>
                </div>

                @if($manutencoes->isEmpty())
                    <p class="text-xs text-gray-500 dark:text-gray-400 text-center py-4">Nenhuma ordem de manutenção registrada para este veículo.</p>
                @else
                    <div class="space-y-3">
                        @foreach($manutencoes as $manut)
                            <div class="rounded-xl border border-gray-100 p-3 dark:border-gray-700/60 dark:bg-gray-700/30">
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="font-bold text-gray-900 dark:text-white">{{ $manut->numero }}</span>
                                    <span class="text-gray-500">{{ $manut->data_agendamento ? $manut->data_agendamento->format('d/m/Y') : '' }}</span>
                                </div>
                                <p class="text-xs text-gray-600 dark:text-gray-300 line-clamp-2">{{ $manut->descricao_servico }}</p>
                                <div class="mt-2 flex items-center justify-between text-[11px]">
                                    <span class="font-semibold text-gray-500">{{ ucfirst($manut->tipo) }}</span>
                                    @if($manut->custo_total > 0)
                                        <span class="font-bold text-gray-900 dark:text-white">R$ {{ number_format($manut->custo_total, 2, ',', '.') }}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Coluna Direita: Diário de Bordo & Abastecimentos (Abas/Cards) -->
        <div class="space-y-6 lg:col-span-2">

            <!-- Card: Diário de Bordo (Viagens Recentes) -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800">
                <div class="flex items-center justify-between border-b border-gray-100 pb-4 dark:border-gray-700 mb-4">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">
                            Diário de Bordo Digital (Saídas e Missões)
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Controle de percurso, motorista, quilometragem e finalidade da viagem.</p>
                    </div>
                    <span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-bold text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                        {{ $viagens->count() }} registros
                    </span>
                </div>

                @if($viagens->isEmpty())
                    <div class="py-8 text-center">
                        <svg class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Nenhuma viagem registrada no diário deste veículo.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-gray-100 dark:border-gray-700 text-gray-400 uppercase font-semibold">
                                    <th class="py-2.5">Nº Viagem</th>
                                    <th class="py-2.5">Data / Saída</th>
                                    <th class="py-2.5">Condutor Oficial</th>
                                    <th class="py-2.5">Destino & Motivo</th>
                                    <th class="py-2.5 text-right">Km Percorrido</th>
                                    <th class="py-2.5 text-center">Situação</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                                @foreach($viagens as $viagem)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">
                                        <td class="py-3 font-mono font-bold text-gray-900 dark:text-white">
                                            {{ $viagem->numero }}
                                        </td>
                                        <td class="py-3 text-gray-600 dark:text-gray-300">
                                            {{ $viagem->data_saida ? $viagem->data_saida->format('d/m/Y H:i') : '--' }}
                                        </td>
                                        <td class="py-3 font-semibold text-gray-800 dark:text-gray-200">
                                            {{ $viagem->motorista ? $viagem->motorista->user->name : 'Não informado' }}
                                        </td>
                                        <td class="py-3">
                                            <p class="font-bold text-gray-900 dark:text-white">{{ $viagem->destino }}</p>
                                            <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate max-w-xs">{{ $viagem->finalidade }}</p>
                                        </td>
                                        <td class="py-3 text-right font-mono font-bold text-gray-900 dark:text-white">
                                            @if($viagem->km_percorrido)
                                                {{ number_format($viagem->km_percorrido, 0, ',', '.') }} km
                                                <span class="block text-[10px] text-gray-400">{{ $viagem->km_saida }} → {{ $viagem->km_retorno }}</span>
                                            @else
                                                <span class="text-blue-500 font-sans font-normal text-[11px]">Em trânsito</span>
                                            @endif
                                        </td>
                                        <td class="py-3 text-center">
                                            @if($viagem->situacao === 'concluida')
                                                <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-[11px] font-bold text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                                    Concluída
                                                </span>
                                            @elseif($viagem->situacao === 'em_andamento')
                                                <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-[11px] font-bold text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 animate-pulse">
                                                    Em Viagem
                                                </span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-[11px] font-bold text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                                    {{ ucfirst($viagem->situacao) }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- Card: Abastecimentos Recentes -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800">
                <div class="flex items-center justify-between border-b border-gray-100 pb-4 dark:border-gray-700 mb-4">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">
                            Histórico de Abastecimentos & Consumo
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Auditoria de postos conveniados, cupons fiscais e rendimento quilométrico.</p>
                    </div>
                    <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                        {{ $abastecimentos->count() }} abastecimentos
                    </span>
                </div>

                @if($abastecimentos->isEmpty())
                    <div class="py-8 text-center">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Nenhum abastecimento lançado ainda.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-gray-100 dark:border-gray-700 text-gray-400 uppercase font-semibold">
                                    <th class="py-2.5">Data</th>
                                    <th class="py-2.5">Combustível</th>
                                    <th class="py-2.5">Posto / Cupom</th>
                                    <th class="py-2.5 text-right">Volume</th>
                                    <th class="py-2.5 text-right">Total (R$)</th>
                                    <th class="py-2.5 text-right">Km Bomba</th>
                                    <th class="py-2.5 text-right">Consumo Médio</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                                @foreach($abastecimentos as $abast)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">
                                        <td class="py-3 font-semibold text-gray-700 dark:text-gray-300">
                                            {{ $abast->data ? $abast->data->format('d/m/Y') : '--' }}
                                        </td>
                                        <td class="py-3 text-gray-900 dark:text-white font-medium">
                                            {{ $abast->combustivel }}
                                        </td>
                                        <td class="py-3">
                                            <p class="font-bold text-gray-900 dark:text-white">{{ $abast->posto_combustivel }}</p>
                                            <p class="text-[10px] text-gray-400 font-mono">{{ $abast->numero_cupom_fiscal ? 'Cupom: ' . $abast->numero_cupom_fiscal : 'Sem cupom' }}</p>
                                        </td>
                                        <td class="py-3 text-right font-mono font-bold text-gray-900 dark:text-white">
                                            {{ number_format($abast->litros, 2, ',', '.') }} L
                                        </td>
                                        <td class="py-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                            R$ {{ number_format($abast->valor_total, 2, ',', '.') }}
                                        </td>
                                        <td class="py-3 text-right font-mono text-gray-600 dark:text-gray-300">
                                            {{ number_format($abast->km_veiculo, 0, ',', '.') }}
                                        </td>
                                        <td class="py-3 text-right">
                                            @if($abast->consumo_medio_kml)
                                                <span class="inline-flex items-center rounded bg-emerald-50 px-2 py-0.5 font-mono text-[11px] font-bold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                                                    {{ number_format($abast->consumo_medio_kml, 2, ',', '.') }} km/l
                                                </span>
                                            @else
                                                <span class="text-gray-400 text-[11px]">--</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>

    </div>

    <!-- Modal Alpine: Registrar Saída (Viagem) -->
    <div x-show="modalSaida" x-cloak class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/60 p-4 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95">
        <div class="relative w-full max-w-xl rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800" @click.away="modalSaida = false">
            <div class="flex items-center justify-between border-b border-gray-100 pb-4 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="rounded-xl bg-blue-100 p-2 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Registrar Saída da Garagem</h3>
                        <p class="text-xs text-gray-500">Veículo: <strong class="text-gray-900 dark:text-white">{{ $veiculo->placa }} ({{ $veiculo->modelo }})</strong></p>
                    </div>
                </div>
                <button type="button" @click="modalSaida = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('frota.viagens.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <input type="hidden" name="veiculo_id" value="{{ $veiculo->id }}">

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                        Condutor Oficial Credenciado <span class="text-red-500">*</span>
                    </label>
                    <select name="motorista_id" required class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Selecione o motorista...</option>
                        @foreach($motoristasAptos as $mot)
                            <option value="{{ $mot->id }}">
                                {{ $mot->user->name }} (CNH {{ $mot->cnh_categoria }} - Venc: {{ $mot->cnh_validade ? $mot->cnh_validade->format('d/m/Y') : 'N/I' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                            Km de Saída <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="km_saida" value="{{ $veiculo->km_atual }}" min="{{ $veiculo->km_atual }}" required
                            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-mono text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                            Data e Horário
                        </label>
                        <input type="datetime-local" name="data_saida" value="{{ now()->format('Y-m-d\TH:i') }}"
                            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                        Destino / Itinerário <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="destino" placeholder="Ex: Hospital Regional, Escola do Campo, Obras Bairro Central" required
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder-gray-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                        Finalidade / Missão Oficial <span class="text-red-500">*</span>
                    </label>
                    <textarea name="finalidade" rows="2" placeholder="Motivo do deslocamento a serviço da Prefeitura..." required
                        class="w-full rounded-xl border border-gray-300 bg-white p-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder-gray-500"></textarea>
                </div>

                <!-- Checklist Rápido de Saída -->
                <div class="rounded-xl bg-gray-50 p-3 dark:bg-gray-700/40 text-xs">
                    <span class="font-bold text-gray-700 dark:text-gray-300 block mb-2">Checklist de Saída da Garagem:</span>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="check_pneus" value="ok" checked class="rounded border-gray-300 text-brand-600">
                            <span>Pneus calibrados</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="check_oleo" value="ok" checked class="rounded border-gray-300 text-brand-600">
                            <span>Óleo / Arrefecimento</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="check_docs" value="ok" checked class="rounded border-gray-300 text-brand-600">
                            <span>CRLV & Seguro OK</span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" @click="modalSaida = false" class="rounded-xl border border-gray-300 px-4 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">
                        Cancelar
                    </button>
                    <button type="submit" class="rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-bold text-white hover:bg-blue-700">
                        Confirmar Saída
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Alpine: Registrar Retorno (se viagem ativa) -->
    @if(isset($viagemAtiva) && $viagemAtiva)
    <div x-show="modalRetorno" x-cloak class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/60 p-4 backdrop-blur-sm">
        <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800" @click.away="modalRetorno = false">
            <div class="flex items-center justify-between border-b border-gray-100 pb-4 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="rounded-xl bg-emerald-100 p-2 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Registrar Retorno à Garagem</h3>
                        <p class="text-xs text-gray-500">Viagem: <strong class="text-gray-900 dark:text-white">{{ $viagemAtiva->numero }}</strong> (Km saída: {{ $viagemAtiva->km_saida }})</p>
                    </div>
                </div>
                <button type="button" @click="modalRetorno = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('frota.viagens.retorno', $viagemAtiva) }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                        Quilometragem no Odômetro ao Retornar <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="km_retorno" value="{{ $viagemAtiva->km_saida + 1 }}" min="{{ $viagemAtiva->km_saida }}" required
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-mono text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <p class="text-[11px] text-gray-500 mt-1">Deve ser maior ou igual a {{ number_format($viagemAtiva->km_saida, 0, ',', '.') }} km.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                        Ocorrências / Avarias na Viagem
                    </label>
                    <textarea name="ocorrencias" rows="2" placeholder="Descreva eventuais problemas mecânicos, multas, furos de pneus ou observações..."
                        class="w-full rounded-xl border border-gray-300 bg-white p-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder-gray-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" @click="modalRetorno = false" class="rounded-xl border border-gray-300 px-4 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">
                        Cancelar
                    </button>
                    <button type="submit" class="rounded-xl bg-emerald-600 px-5 py-2.5 text-xs font-bold text-white hover:bg-emerald-700">
                        Concluir e Liberar Veículo
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Modal Alpine: Registrar Abastecimento -->
    <div x-show="modalAbastecimento" x-cloak class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/60 p-4 backdrop-blur-sm">
        <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800" @click.away="modalAbastecimento = false">
            <div class="flex items-center justify-between border-b border-gray-100 pb-4 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="rounded-xl bg-amber-100 p-2 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Lançar Abastecimento</h3>
                        <p class="text-xs text-gray-500">Veículo: <strong class="text-gray-900 dark:text-white">{{ $veiculo->placa }}</strong> ({{ $veiculo->combustivel }})</p>
                    </div>
                </div>
                <button type="button" @click="modalAbastecimento = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('frota.abastecimentos.store') }}" method="POST" class="mt-5 space-y-4" x-data="{ litros: 0, valorLitro: 0 }">
                @csrf
                <input type="hidden" name="veiculo_id" value="{{ $veiculo->id }}">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                            Data <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="data" value="{{ date('Y-m-d') }}" required
                            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                            Combustível <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="combustivel" value="{{ $veiculo->combustivel }}" required
                            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                            Litros <span class="text-red-500">*</span>
                        </label>
                        <input type="number" step="0.01" name="litros" x-model="litros" placeholder="0.00" required
                            class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm font-mono text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                            Preço/L (R$) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" step="0.001" name="valor_litro" x-model="valorLitro" placeholder="0.000" required
                            class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm font-mono text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                            Total Estimado
                        </label>
                        <div class="flex items-center h-10 px-3 rounded-xl bg-gray-100 dark:bg-gray-700 font-mono font-bold text-xs text-gray-900 dark:text-white">
                            R$ <span x-text="(litros * valorLitro).toFixed(2)">0.00</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                            Km no Odômetro <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="km_veiculo" value="{{ $veiculo->km_atual }}" min="{{ $veiculo->km_atual }}" required
                            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-mono text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                            Nº Cupom / NF
                        </label>
                        <input type="text" name="numero_cupom_fiscal" placeholder="Ex: NF-e 45291"
                            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                        Posto Conveniado / Fornecedor <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="posto_combustivel" placeholder="Ex: Auto Posto Municipal Ltda / Ipiranga" required
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder-gray-500">
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" @click="modalAbastecimento = false" class="rounded-xl border border-gray-300 px-4 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">
                        Cancelar
                    </button>
                    <button type="submit" class="rounded-xl bg-amber-600 px-5 py-2.5 text-xs font-bold text-white hover:bg-amber-700">
                        Salvar Abastecimento
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
