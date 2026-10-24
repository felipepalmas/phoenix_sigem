<?php

/**
 * Componente da aplicação LivreOS
 *
 * @author    viniciusvams
 * @copyright 2024-2026 LivreOS
 * @license   https://www.gnu.org/licenses/agpl-3.0.txt AGPL-3.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 */

namespace App\Http\Controllers\Financeiro;

use App\Http\Controllers\Controller;
use App\Models\CentroCusto;
use Illuminate\Http\Request;

class CentroCustoController extends Controller
{
    public function index()
    {
        $query = CentroCusto::orderBy('ordem')->orderBy('nome');
        if (method_exists($this, 'applyEntityQuery')) {
            $query = $this->applyEntityQuery($query, 'centro_custo');
        }
        $centros = $query->paginate(15);

        return erp_view('financeiro.centros-custo.index', [
            'title' => 'Secretarias e Departamentos / Setores',
            'centros' => $centros,
        ]);
    }

    public function create()
    {
        return erp_view('financeiro.centros-custo.create', [
            'title' => 'Nova Secretaria / Setor',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateWithFilters($request, [
            'codigo' => 'nullable|string|max:30',
            'nome' => 'required|string|max:200',
            'descricao' => 'nullable|string',
            'ativo' => 'boolean',
            'ordem' => 'nullable|integer|min:0',
        ]);

        CentroCusto::create([
            'codigo' => $validated['codigo'] ?? null,
            'nome' => $validated['nome'],
            'descricao' => $validated['descricao'] ?? null,
            'ativo' => $request->boolean('ativo', true),
            'ordem' => $validated['ordem'] ?? 0,
            'created_by' => auth()->id(),
        ]);

        $route = request()->routeIs('secretarias.*') ? 'secretarias.index' : (request()->routeIs('centros-custo.*') ? 'centros-custo.index' : 'financeiro.centros-custo.index');
        return redirect()->route($route)
            ->with('success', 'Secretaria / Setor cadastrado com sucesso.');
    }

    public function edit(CentroCusto $centroCusto)
    {
        return erp_view('financeiro.centros-custo.edit', [
            'title' => 'Editar Secretaria / Setor',
            'centro' => $centroCusto,
        ]);
    }

    public function update(Request $request, CentroCusto $centroCusto)
    {
        $validated = $this->validateWithFilters($request, [
            'codigo' => 'nullable|string|max:30',
            'nome' => 'required|string|max:200',
            'descricao' => 'nullable|string',
            'ativo' => 'boolean',
            'ordem' => 'nullable|integer|min:0',
        ]);

        $centroCusto->update([
            'codigo' => $validated['codigo'] ?? null,
            'nome' => $validated['nome'],
            'descricao' => $validated['descricao'] ?? null,
            'ativo' => $request->boolean('ativo', true),
            'ordem' => $validated['ordem'] ?? 0,
            'updated_by' => auth()->id(),
        ]);

        $route = request()->routeIs('secretarias.*') ? 'secretarias.index' : (request()->routeIs('centros-custo.*') ? 'centros-custo.index' : 'financeiro.centros-custo.index');
        return redirect()->route($route)
            ->with('success', 'Secretaria / Setor atualizado com sucesso.');
    }

    public function destroy(CentroCusto $centroCusto)
    {
        $centroCusto->delete();
        $route = request()->routeIs('secretarias.*') ? 'secretarias.index' : (request()->routeIs('centros-custo.*') ? 'centros-custo.index' : 'financeiro.centros-custo.index');
        return redirect()->route($route)
            ->with('success', 'Secretaria / Setor excluído.');
    }
}
