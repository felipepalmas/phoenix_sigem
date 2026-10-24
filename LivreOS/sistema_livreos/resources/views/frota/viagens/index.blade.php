{{--
 * Componente da aplicação Phoenix - SIGEM
 * Diário de Bordo Digital da Frota Municipal (Controle de Saídas e Retornos)
 *
 * @license AGPL-3.0
 --}}

@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ modalNovaSaida: false, modalRetorno: false, viagemAtivaModal: null }">

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

    <!-- Header do Diário de Bordo -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <nav class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-1">
                    <a href="{{ route('frota.index') }}" class="hover:text-brand-600 transition">Frota Municipal</a>
                    <span>/</span>
                    <span class="text-gray-900 dark:text-white font-semibold">Diário de Bordo Digital</span>
                </nav>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white sm:text-3xl">
                    Diário de Bordo Digital
                </h1>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                    Registro obrigatório e auditável de saídas, itinerários, motoristas credenciados e retornos de viaturas à garagem oficial.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('frota.index') }}" class="rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-xs font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition">
                    ← Painel da Garagem
                </a>
                <button type="button" @click="modalNovaSaida = true" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-blue-700 transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nova Saída de Veículo
                </button>
            </div>
        </div>
    </div>

    <!-- Filtros de Busca -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800">
        <form method="GET" action="{{ route('frota.viagens.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-4">
            <div class="sm:col-span-2">
                <input type="text" name="busca" value="{{ request('busca') }}" placeholder="Buscar por número, placa, modelo, destino ou motorista..."
                    class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-xs text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder-gray-500">
            </div>

            <div>
                <select name="situacao" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-xs text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">Todas as Situações</option>
                    <option value="em_andamento" {{ request('situacao') == 'em_andamento' ? 'selected' : '' }}>Em Andamento (Na Rua)</option>
                    <option value="concluida" {{ request('situacao') == 'concluida' ? 'selected' : '' }}>Concluídas (Na Garagem)</option>
                    <option value="cancelada" {{ request('situacao') == 'cancelada' ? 'selected' : '' }}>Canceladas</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 rounded-xl bg-gray-900 px-4 py-2.5 text-xs font-bold text-white hover:bg-black dark:bg-gray-700 dark:hover:bg-gray-600 transition">
                    Filtrar
                </button>
                @if(request()->anyFilled(['busca', 'situacao']))
                    <a href="{{ route('frota.viagens.index') }}" class="rounded-xl border border-gray-300 px-3 py-2.5 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">
                        Limpar
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabela do Diário de Bordo -->
    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-sm overflow-hidden dark:border-gray-800 dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50/75 dark:border-gray-700 dark:bg-gray-900/40 text-gray-500 dark:text-gray-400 uppercase font-semibold">
                        <th class="py-3 px-4">Nº Registro</th>
                        <th class="py-3 px-4">Veículo Oficial</th>
                        <th class="py-3 px-4">Condutor Oficial</th>
                        <th class="py-3 px-4">Destino & Finalidade</th>
                        <th class="py-3 px-4">Saída / Retorno</th>
                        <th class="py-3 px-4 text-right">Km Inicial / Final</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Ação</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                    @forelse($viagens as $viagem)
                        <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-700/40 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-gray-900 dark:text-white">
                                {{ $viagem->numero }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if($viagem->veiculo)
                                    <a href="{{ route('frota.show', $viagem->veiculo) }}" class="font-bold text-brand-600 hover:underline dark:text-brand-400">
                                        {{ $viagem->veiculo->placa }}
                                    </a>
                                    <span class="block text-[11px] text-gray-500 dark:text-gray-400">{{ $viagem->veiculo->modelo }}</span>
                                @else
                                    <span class="text-gray-400">Veículo removido</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-gray-900 dark:text-white">{{ $viagem->motorista ? $viagem->motorista->user->name : 'N/I' }}</span>
                                @if($viagem->motorista)
                                    <span class="block text-[10px] text-gray-400">CNH {{ $viagem->motorista->cnh_categoria }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <p class="font-bold text-gray-900 dark:text-white">{{ $viagem->destino }}</p>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate max-w-xs">{{ $viagem->finalidade }}</p>
                            </td>
                            <td class="py-3.5 px-4 text-gray-600 dark:text-gray-300">
                                <p><span class="text-gray-400">Saída:</span> {{ $viagem->data_saida ? $viagem->data_saida->format('d/m/Y H:i') : '--' }}</p>
                                @if($viagem->data_retorno)
                                    <p class="text-[11px] text-emerald-600 dark:text-emerald-400"><span class="text-gray-400">Retorno:</span> {{ $viagem->data_retorno->format('d/m/Y H:i') }}</p>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono">
                                @if($viagem->km_percorrido)
                                    <span class="font-bold text-gray-900 dark:text-white text-sm">{{ number_format($viagem->km_percorrido, 0, ',', '.') }} km</span>
                                    <span class="block text-[10px] text-gray-400">{{ $viagem->km_saida }} → {{ $viagem->km_retorno }}</span>
                                @else
                                    <span class="font-bold text-blue-600 dark:text-blue-400">{{ number_format($viagem->km_saida, 0, ',', '.') }} km</span>
                                    <span class="block text-[10px] text-gray-400">Em curso</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($viagem->situacao === 'concluida')
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-[11px] font-bold text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                        Concluída
                                    </span>
                                @elseif($viagem->situacao === 'em_andamento')
                                    <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-[11px] font-bold text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 animate-pulse">
                                        Em Trânsito
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-[11px] font-bold text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                        {{ ucfirst($viagem->situacao) }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                @if($viagem->situacao === 'em_andamento')
                                    <button type="button" @click="viagemAtivaModal = { id: {{ $viagem->id }}, numero: '{{ $viagem->numero }}', km_saida: {{ $viagem->km_saida }}, placa: '{{ $viagem->veiculo->placa ?? '' }}' }; modalRetorno = true"
                                        class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-2.5 py-1 text-[11px] font-bold text-white hover:bg-emerald-700 transition">
                                        Registrar Retorno
                                    </button>
                                @else
                                    <span class="text-gray-400 text-[11px]">Arquivado</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-gray-500 dark:text-gray-400">
                                <p class="text-sm font-semibold">Nenhuma viagem encontrada no Diário de Bordo.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($viagens->hasPages())
            <div class="border-t border-gray-200 p-4 dark:border-gray-700">
                {{ $viagens->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Nova Saída (A partir de qualquer veículo disponível) -->
    <div x-show="modalNovaSaida" x-cloak class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/60 p-4 backdrop-blur-sm">
        <div class="relative w-full max-w-xl rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800" @click.away="modalNovaSaida = false">
            <div class="flex items-center justify-between border-b border-gray-100 pb-4 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="rounded-xl bg-blue-100 p-2 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Abrir Saída no Diário de Bordo</h3>
                        <p class="text-xs text-gray-500">Selecione o veículo disponível e o condutor credenciado.</p>
                    </div>
                </div>
                <button type="button" @click="modalNovaSaida = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('frota.viagens.store') }}" method="POST" class="mt-5 space-y-4" x-data="{ veiculos: {{ json_encode($veiculosDisponiveis) }}, selectedVeiculoId: '', selectedKm: 0 }">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                        Veículo Disponível na Garagem <span class="text-red-500">*</span>
                    </label>
                    <select name="veiculo_id" x-model="selectedVeiculoId" @change="let v = veiculos.find(x => x.id == selectedVeiculoId); selectedKm = v ? v.km_atual : 0" required
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Selecione a viatura...</option>
                        @foreach($veiculosDisponiveis as $v)
                            <option value="{{ $v->id }}">
                                {{ $v->placa }} - {{ $v->modelo }} (Odômetro: {{ number_format($v->km_atual, 0, ',', '.') }} km)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                        Condutor Oficial Credenciado <span class="text-red-500">*</span>
                    </label>
                    <select name="motorista_id" required class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Selecione o motorista...</option>
                        @foreach($motoristasAptos as $mot)
                            <option value="{{ $mot->id }}">
                                {{ $mot->user->name }} (CNH {{ $mot->cnh_categoria }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                            Km de Saída <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="km_saida" x-model="selectedKm" min="0" required
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
                    <input type="text" name="destino" placeholder="Ex: Hospital Municipal, Unidades de Saúde do Interior" required
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder-gray-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                        Finalidade / Missão Oficial <span class="text-red-500">*</span>
                    </label>
                    <textarea name="finalidade" rows="2" placeholder="Motivo do deslocamento a serviço público..." required
                        class="w-full rounded-xl border border-gray-300 bg-white p-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder-gray-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" @click="modalNovaSaida = false" class="rounded-xl border border-gray-300 px-4 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">
                        Cancelar
                    </button>
                    <button type="submit" class="rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-bold text-white hover:bg-blue-700">
                        Registrar Saída
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Retorno Dinâmico -->
    <div x-show="modalRetorno" x-cloak class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/60 p-4 backdrop-blur-sm">
        <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800" @click.away="modalRetorno = false">
            <template x-if="viagemAtivaModal">
                <div>
                    <div class="flex items-center justify-between border-b border-gray-100 pb-4 dark:border-gray-700">
                        <div class="flex items-center gap-3">
                            <div class="rounded-xl bg-emerald-100 p-2 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-900 dark:text-white">Registrar Retorno à Garagem</h3>
                                <p class="text-xs text-gray-500">Viagem <strong x-text="viagemAtivaModal.numero"></strong> • Placa: <strong x-text="viagemAtivaModal.placa"></strong></p>
                            </div>
                        </div>
                        <button type="button" @click="modalRetorno = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form :action="'/frota/diario-bordo/' + viagemAtivaModal.id + '/retorno'" method="POST" class="mt-5 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                                Quilometragem no Retorno (Km) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="km_retorno" :min="viagemAtivaModal.km_saida" :value="viagemAtivaModal.km_saida + 1" required
                                class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-mono text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <p class="text-[11px] text-gray-500 mt-1">Km de saída foi: <span x-text="viagemAtivaModal.km_saida"></span> km.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                                Ocorrências / Avarias
                            </label>
                            <textarea name="ocorrencias" rows="2" placeholder="Observações de retorno, pneus, ruídos mecânicos..."
                                class="w-full rounded-xl border border-gray-300 bg-white p-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder-gray-500"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                            <button type="button" @click="modalRetorno = false" class="rounded-xl border border-gray-300 px-4 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">
                                Cancelar
                            </button>
                            <button type="submit" class="rounded-xl bg-emerald-600 px-5 py-2.5 text-xs font-bold text-white hover:bg-emerald-700">
                                Concluir Retorno
                            </button>
                        </div>
                    </form>
                </div>
            </template>
        </div>
    </div>

</div>
@endsection
