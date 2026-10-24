<?php

/**
 * Componente da aplicação Phoenix - SIGEM
 *
 * @license AGPL-3.0
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Desativar um usuário encerra o acesso na próxima requisição,
 * inclusive de quem já estava logado ou usava "manter conectado".
 */
class EncerrarSessaoDeUsuarioInativo
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if ($user && isset($user->ativo) && ! $user->ativo) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('signin')->withErrors(['email' => 'Seu usuário foi desativado. Procure o setor de TI da Prefeitura.']);
        }

        return $next($request);
    }
}
