<?php

/**
 * Componente da aplicação Phoenix - SIGEM
 * Painel de Auditoria de Processos Sigilosos e Restritos (LAI / LGPD)
 *
 * @license AGPL-3.0
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditProcessoSigiloso;
use App\Models\Processo;
use App\Models\User;
use Illuminate\Http\Request;

class AuditProcessoSigilosoController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditProcessoSigiloso::with(['processo', 'user', 'unidade'])
            ->orderByDesc('created_at');

        if ($request->filled('processo_numero')) {
            $numero = trim($request->input('processo_numero'));
            $query->whereHas('processo', function ($q) use ($numero) {
                $q->where('numero', 'like', "%{$numero}%");
            });
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('acao')) {
            $query->where('acao', $request->input('acao'));
        }

        if ($request->filled('data_inicio')) {
            $query->whereDate('created_at', '>=', $request->input('data_inicio'));
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('created_at', '<=', $request->input('data_fim'));
        }

        $items = $query->paginate(25)->withQueryString();
        $users = User::orderBy('name')->get(['id', 'name']);

        return erp_view('admin.audit-processos-sigilosos.index', [
            'title' => 'Auditoria de Processos Sigilosos (LAI / LGPD)',
            'items' => $items,
            'users' => $users,
        ]);
    }
}
