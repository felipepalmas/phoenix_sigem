<?php

/**
 * Componente da aplicação Phoenix - SIGEM
 * Middleware: Controle de Módulos Habilitados / Feature Flags
 *
 * @license AGPL-3.0
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $modulo): Response
    {
        $ativo = config("modulos.{$modulo}", false);

        if (! $ativo) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => "O módulo '{$modulo}' está desativado na configuração do sistema Phoenix - SIGEM.",
                    'error' => 'module_disabled',
                ], 403);
            }

            return redirect()->route('dashboard')
                ->with('error', "O módulo '{$modulo}' está desativado no ambiente municipal Phoenix - SIGEM.");
        }

        return $next($request);
    }
}
