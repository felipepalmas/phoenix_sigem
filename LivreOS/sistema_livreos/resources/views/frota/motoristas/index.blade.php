{{--
 * Componente da aplicação Phoenix - SIGEM
 * Gestão e Credenciamento de Motoristas Oficiais do Município
 *
 * @license AGPL-3.0
 --}}

@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ modalNovoMotorista: false }">

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

    <!-- Header dos Motoristas Oficiais -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <nav class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-1">
                    <a href="{{ route('frota.index') }}" class="hover:text-brand-600 transition">Frota Municipal</a>
                    <span>/</span>
                    <span class="text-gray-900 dark:text-white font-semibold">Motoristas Oficiais</span>
                </nav>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white sm:text-3xl">
                    Motoristas & Condutores Oficiais
                </h1>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                    Controle de habilitação (CNH), validades, cursos de capacitação (SAMU, Transporte Escolar, MOPP) e conformidade legal.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('frota.index') }}" class="rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-xs font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition">
                    ← Garagem Central
                </a>
                <button type="button" @click="modalNovoMotorista = true" class="inline-flex items-center gap-2 rounded-xl bg-purple-600 px-5 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-purple-700 transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    Credenciar Motorista
                </button>
            </div>
        </div>
    </div>

    <!-- Lista de Motoristas Oficiais -->
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
        @forelse($motoristas as $motorista)
            @php
                $diasValidade = $motorista->cnh_validade ? now()->diffInDays($motorista->cnh_validade, false) : null;
                $vencida = $diasValidade !== null && $diasValidade < 0;
                $vencendo = $diasValidade !== null && $diasValidade >= 0 && $diasValidade <= 30;
            @endphp
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800 flex flex-col justify-between">
                <div>
                    <!-- Topo do Card -->
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-purple-100 font-bold text-purple-700 dark:bg-purple-900/40 dark:text-purple-300">
                                {{ strtoupper(substr($motorista->user->name ?? 'M', 0, 2)) }}
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                                    {{ $motorista->user->name ?? 'Usuário' }}
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $motorista->user->email ?? '' }}</p>
                            </div>
                        </div>

                        <!-- Categoria CNH Badge -->
                        <span class="inline-flex items-center rounded-lg border border-purple-200 bg-purple-50 px-2.5 py-1 text-xs font-black text-purple-800 dark:border-purple-800 dark:bg-purple-900/40 dark:text-purple-300">
                            CAT {{ $motorista->cnh_categoria }}
                        </span>
                    </div>

                    <!-- Dados da CNH -->
                    <div class="rounded-xl bg-gray-50 p-3 dark:bg-gray-700/40 space-y-1.5 text-xs">
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Nº da CNH:</span>
                            <span class="font-mono font-bold text-gray-900 dark:text-white">{{ $motorista->cnh_numero }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500 dark:text-gray-400">Validade CNH:</span>
                            <div>
                                <span class="font-bold text-gray-900 dark:text-white">{{ $motorista->cnh_validade ? $motorista->cnh_validade->format('d/m/Y') : '--' }}</span>
                                @if($vencida)
                                    <span class="ml-1 inline-flex items-center rounded bg-red-100 px-1.5 py-0.2 text-[10px] font-bold text-red-800 dark:bg-red-900/50 dark:text-red-300">
                                        VENCIDA
                                    </span>
                                @elseif($vencendo)
                                    <span class="ml-1 inline-flex items-center rounded bg-amber-100 px-1.5 py-0.2 text-[10px] font-bold text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">
                                        Expira em {{ $diasValidade }}d
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Status Operacional:</span>
                            <span class="font-bold {{ $motorista->status === 'apto' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600' }}">
                                {{ ucfirst($motorista->status) }}
                            </span>
                        </div>
                    </div>

                    <!-- Cursos e Certificações Obrigatórias -->
                    <div class="mt-4">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400 block mb-2">Cursos & Especializações:</span>
                        @if(!empty($motorista->cursos_obrigatorios) && is_array($motorista->cursos_obrigatorios))
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($motorista->cursos_obrigatorios as $curso)
                                    <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-[10px] font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                        ✓ {{ $curso }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-[11px] text-gray-400 italic">Nenhum curso complementar cadastrado</span>
                        @endif
                    </div>
                </div>

                <!-- Rodapé do Card com Viagens Realizadas -->
                <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between text-xs">
                    <span class="text-gray-500">Missões registradas:</span>
                    <span class="font-bold text-gray-900 dark:text-white">{{ $motorista->viagens()->count() }} viagens</span>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-2xl border border-gray-200 bg-white p-12 text-center shadow-theme-sm dark:border-gray-800 dark:bg-gray-800">
                <p class="text-sm font-semibold text-gray-500">Nenhum motorista oficial credenciado no sistema.</p>
            </div>
        @endforelse
    </div>

    @if($motoristas->hasPages())
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-sm dark:border-gray-800 dark:bg-gray-800">
            {{ $motoristas->links() }}
        </div>
    @endif

    <!-- Modal Alpine: Credenciar Novo Motorista -->
    <div x-show="modalNovoMotorista" x-cloak class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/60 p-4 backdrop-blur-sm">
        <div class="relative w-full max-w-xl rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800" @click.away="modalNovoMotorista = false">
            <div class="flex items-center justify-between border-b border-gray-100 pb-4 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="rounded-xl bg-purple-100 p-2 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Credenciar Condutor Oficial</h3>
                        <p class="text-xs text-gray-500">Vincule um servidor do sistema com suas credenciais de trânsito.</p>
                    </div>
                </div>
                <button type="button" @click="modalNovoMotorista = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('frota.motoristas.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                        Servidor / Usuário do Sistema <span class="text-red-500">*</span>
                    </label>
                    <select name="user_id" required class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Selecione o servidor...</option>
                        @foreach($usuariosDisponiveis as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                            Número da CNH <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="cnh_numero" placeholder="Ex: 01234567890" required
                            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-mono text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                            Categoria <span class="text-red-500">*</span>
                        </label>
                        <select name="cnh_categoria" required class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm font-bold text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <option value="B">B</option>
                            <option value="AB">AB</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                            <option value="E">E</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                        Data de Validade da CNH <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="cnh_validade" required
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>

                <!-- Cursos e Certificações -->
                <div>
                    <span class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                        Cursos de Especialização Válidos
                    </span>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <label class="flex items-center gap-2 p-2 rounded-lg border border-gray-200 dark:border-gray-700">
                            <input type="checkbox" name="cursos_obrigatorios[]" value="Transporte de Emergência (SAMU)" class="rounded text-purple-600">
                            <span>Transporte de Emergência (SAMU)</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 rounded-lg border border-gray-200 dark:border-gray-700">
                            <input type="checkbox" name="cursos_obrigatorios[]" value="Transporte Escolar" class="rounded text-purple-600">
                            <span>Transporte Escolar</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 rounded-lg border border-gray-200 dark:border-gray-700">
                            <input type="checkbox" name="cursos_obrigatorios[]" value="Cargas Perigosas (MOPP)" class="rounded text-purple-600">
                            <span>Cargas Perigosas (MOPP)</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 rounded-lg border border-gray-200 dark:border-gray-700">
                            <input type="checkbox" name="cursos_obrigatorios[]" value="Operador de Máquinas Pesadas" class="rounded text-purple-600">
                            <span>Operador de Máquinas Pesadas</span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" @click="modalNovoMotorista = false" class="rounded-xl border border-gray-300 px-4 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">
                        Cancelar
                    </button>
                    <button type="submit" class="rounded-xl bg-purple-600 px-5 py-2.5 text-xs font-bold text-white hover:bg-purple-700">
                        Confirmar Credenciamento
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
