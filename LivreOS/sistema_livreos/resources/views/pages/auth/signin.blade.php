{{--
 * Componente da aplicação Phoenix - SIGEM
 * Entrada do servidor (padrão SIGEM Clean)
 *
 * @license AGPL-3.0
 --}}
@extends('layouts.fullscreen-layout')

@section('content')
@php
    $orgao = \App\Models\Empresa::first();
    $orgaoNome = $orgao?->nome ?: ($orgao?->razao_social ?: 'Prefeitura Municipal');
    $govbrAtivo = (bool) config('govbr.enabled') && filled(config('govbr.client_id'));
@endphp
<div class="flex min-h-screen bg-sigem-canvas dark:bg-gray-950">
    <main class="flex w-full flex-col items-center justify-center px-4 py-10 lg:w-1/2">
        <div class="w-full max-w-[400px]">
            <div class="mb-7 flex items-center gap-3">
                <img src="{{ asset(config('app.icon_path', 'logo/icone.png')) }}" alt="" class="h-11 w-11 rounded-md object-contain">
                <div class="leading-tight">
                    <div class="text-lg font-semibold text-sigem-ink dark:text-white">Phoenix SIGEM</div>
                    <div class="text-[13px] text-sigem-muted dark:text-gray-400">{{ $orgaoNome }}</div>
                </div>
            </div>

            <h1 class="m-0 text-[22px] font-semibold text-sigem-ink dark:text-white">Entrar</h1>
            <p class="m-0 mt-1 text-sigem-muted dark:text-gray-400">Use seu e-mail e senha de servidor.</p>

            <div class="mt-6">
                <x-sigem.flash />
            </div>

            <form action="{{ route('login') }}" method="POST" class="flex flex-col gap-4" x-data="{ ver: false }">
                @csrf
                <input type="hidden" name="login_form_token" value="{{ $loginFormToken ?? '' }}">
                <div style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden;" aria-hidden="true">
                    <label for="website">Site</label>
                    <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                </div>

                <x-sigem.field label="E-mail" for="email" required>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="nome@prefeitura.rs.gov.br" class="sigem-input h-10">
                </x-sigem.field>

                <div>
                    <label for="password" class="sigem-label">Senha <span class="text-sigem-danger" aria-hidden="true">*</span></label>
                    <div class="relative">
                        <input :type="ver ? 'text' : 'password'" type="password" id="password" name="password" required autocomplete="current-password" class="sigem-input h-10 pr-11">
                        <button type="button" @click="ver = !ver" :aria-label="ver ? 'Ocultar senha' : 'Mostrar senha'" aria-label="Mostrar senha"
                            class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-sigem-muted hover:text-sigem-ink dark:text-gray-400">
                            <svg aria-hidden="true" class="h-[18px] w-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 text-sm">
                    <label class="flex items-center gap-2"><input type="checkbox" name="remember" @checked(old('remember'))> Manter conectado</label>
                    <span class="text-[13px] text-sigem-muted dark:text-gray-400">Esqueceu a senha? Fale com o TI.</span>
                </div>

                <x-sigem.button type="submit" variant="primary" class="h-10 w-full">Entrar</x-sigem.button>
            </form>

            @if($govbrAtivo)
                <div class="my-5 flex items-center gap-3 text-[13px] text-sigem-muted"><span class="h-px flex-1 bg-sigem-line dark:bg-gray-800"></span>ou<span class="h-px flex-1 bg-sigem-line dark:bg-gray-800"></span></div>
                <a href="{{ route('auth.govbr.redirect') }}" class="sigem-btn h-10 w-full bg-[#1351b4] text-white hover:bg-[#0c3d8a] hover:text-white">
                    Entrar com <strong class="ml-1">gov.br</strong>
                </a>
            @endif

            <p class="mt-8 border-t border-sigem-line pt-4 text-[13px] text-sigem-muted dark:border-gray-800 dark:text-gray-400">
                É cidadão? Peça serviços e acompanhe processos no <a href="{{ route('portal.index') }}" class="sigem-link font-normal">Portal do Cidadão</a>.
            </p>
        </div>
    </main>
    <aside class="hidden w-1/2 items-center justify-center bg-[#0b2a5b] p-12 lg:flex" aria-hidden="true">
        <div class="max-w-sm text-white">
            <img src="{{ asset(config('app.logo_path', 'logo/phoenix_logo.png')) }}" alt="" class="mb-8 h-auto w-60">
            <p class="m-0 text-xl font-semibold leading-snug">Processos, zeladoria, almoxarifado e frota no mesmo lugar.</p>
            <p class="m-0 mt-3 text-blue-100">Sem papel, com histórico e prazo de cada pedido.</p>
        </div>
    </aside>
</div>
@endsection
