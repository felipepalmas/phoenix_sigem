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

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Rules\CpfValido;
use App\Services\AuditCancelExcluirService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles');
        if ($request->filled('excluidos') && $request->get('excluidos') === '1') {
            $query->onlyTrashed();
        }
        $query = $this->applyEntityQuery($query, 'user');
        $users = $query->orderBy('name')->paginate(15)->withQueryString();

        return erp_view('admin.users.index', [
            'title' => 'Gerenciar Usuários',
            'users' => $users,
        ]);
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();
        $unidades = \App\Models\UnidadeAdministrativa::where('ativo', true)->orderBy('nome')->get(['id', 'nome', 'sigla']);
        $viewData = function_exists('apply_filters') ? apply_filters('admin.users.create.data', [
            'title' => 'Criar Usuário',
            'roles' => $roles,
            'unidades' => $unidades,
        ]) : ['title' => 'Criar Usuário', 'roles' => $roles, 'unidades' => $unidades];
        return erp_view('admin.users.create', $viewData);
    }

    /**
     * Quem não é gestor geral (ex.: chefia com manage-users) não pode conceder o papel "admin",
     * nem mexer em lotação ou nos próprios papéis — senão escaparia do escopo da sua secretaria.
     */
    protected function papeisPermitidos(array $roleIds): array
    {
        if (auth()->user()->ehGestorGeral()) {
            return $roleIds;
        }
        $adminId = \App\Models\Role::where('slug', 'admin')->value('id');

        return array_values(array_filter($roleIds, fn ($id) => (int) $id !== (int) $adminId));
    }

    protected function protegerGestorGeral(User $alvo): void
    {
        if (! auth()->user()->ehGestorGeral() && $alvo->ehGestorGeral()) {
            abort(403, 'Somente um administrador pode alterar a conta de outro administrador.');
        }
    }

    public function store(Request $request)
    {
        if ($request->filled('cpf')) {
            $request->merge(['cpf' => $this->normalizarCpf($request->input('cpf'))]);
        }
        $baseRules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'is_admin' => 'boolean',
            'ativo' => 'boolean',
            'roles' => 'array',
            'cpf' => ['nullable', 'string', 'max:20', new CpfValido, 'unique:users,cpf'],
            'rg' => 'nullable|string|max:20',
            'rg_orgao_emissor' => 'nullable|string|max:20',
            'telefone' => 'nullable|string|max:20',
            'cargo' => 'nullable|string|max:100',
            'departamento' => 'nullable|string|max:100',
            'unidade_administrativa_id' => 'nullable|integer|exists:unidades_administrativas,id',
            'custo_hora_tecnica' => 'nullable|numeric|min:0',
            'cep' => 'nullable|string|max:10',
            'logradouro' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'complemento' => 'nullable|string|max:100',
            'bairro' => 'nullable|string|max:100',
            'cidade' => 'nullable|string|max:100',
            'uf' => 'nullable|string|size:2',
        ];
        $rules = function_exists('apply_filters') ? apply_filters('admin.user.store.rules', $baseRules, $request) : $baseRules;
        $validated = $this->validateWithFilters($request, $rules);

        // Apenas administradores podem criar usuário como admin; demais sempre criam como não-admin
        $isAdmin = auth()->user()->is_admin && $request->boolean('is_admin');
        if (! auth()->user()->ehGestorGeral()) {
            // Chefia só cadastra servidores na própria secretaria
            $request->merge(['unidade_administrativa_id' => auth()->user()->unidade_administrativa_id]);
        }

        $createData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'ativo' => $request->boolean('ativo', true),
            'cpf' => $this->normalizarCpf($request->input('cpf')),
            'rg' => $request->input('rg'),
            'rg_orgao_emissor' => $request->input('rg_orgao_emissor'),
            'telefone' => $request->input('telefone'),
            'cargo' => $request->input('cargo'),
            'departamento' => $request->input('departamento'),
            'unidade_administrativa_id' => $request->filled('unidade_administrativa_id') ? (int) $request->input('unidade_administrativa_id') : null,
            'custo_hora_tecnica' => $request->filled('custo_hora_tecnica') ? $request->input('custo_hora_tecnica') : null,
            'cep' => $request->input('cep'),
            'logradouro' => $request->input('logradouro'),
            'numero' => $request->input('numero'),
            'complemento' => $request->input('complemento'),
            'bairro' => $request->input('bairro'),
            'cidade' => $request->input('cidade'),
            'uf' => $request->input('uf') ? strtoupper(substr($request->input('uf'), 0, 2)) : null,
        ];
        $extraData = function_exists('apply_filters') ? apply_filters('admin.user.store.data', [], $request, $validated) : [];
        $user = User::create(array_merge($createData, $extraData));
        $user->is_admin = $isAdmin;
        $user->save();

        if ($request->has('roles')) {
            $user->roles()->sync($this->papeisPermitidos((array) $request->roles));
        }

        if (function_exists('do_action')) {
            do_action('admin.user.stored', $user, $request);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário criado com sucesso!');
    }

    public function edit(User $user)
    {
        $this->protegerGestorGeral($user);
        $roles = Role::orderBy('name')->get();
        $unidades = \App\Models\UnidadeAdministrativa::where('ativo', true)->orderBy('nome')->get(['id', 'nome', 'sigla']);
        $viewData = function_exists('apply_filters') ? apply_filters('admin.users.edit.data', [
            'title' => 'Editar Usuário',
            'user' => $user,
            'roles' => $roles,
            'unidades' => $unidades,
        ], $user) : ['title' => 'Editar Usuário', 'user' => $user, 'roles' => $roles, 'unidades' => $unidades];
        return erp_view('admin.users.edit', $viewData);
    }

    public function update(Request $request, User $user)
    {
        $this->protegerGestorGeral($user);
        $editorGestor = auth()->user()->ehGestorGeral();
        if (! $editorGestor) {
            // Lotação só é alterada pelo administrador
            $request->merge(['unidade_administrativa_id' => $user->unidade_administrativa_id]);
        }
        if ($request->filled('cpf')) {
            $request->merge(['cpf' => $this->normalizarCpf($request->input('cpf'))]);
        }
        $baseRules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'is_admin' => 'boolean',
            'ativo' => 'boolean',
            'roles' => 'array',
            'cpf' => ['nullable', 'string', 'max:20', new CpfValido, 'unique:users,cpf,' . $user->id],
            'rg' => 'nullable|string|max:20',
            'rg_orgao_emissor' => 'nullable|string|max:20',
            'telefone' => 'nullable|string|max:20',
            'cargo' => 'nullable|string|max:100',
            'departamento' => 'nullable|string|max:100',
            'unidade_administrativa_id' => 'nullable|integer|exists:unidades_administrativas,id',
            'custo_hora_tecnica' => 'nullable|numeric|min:0',
            'cep' => 'nullable|string|max:10',
            'logradouro' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'complemento' => 'nullable|string|max:100',
            'bairro' => 'nullable|string|max:100',
            'cidade' => 'nullable|string|max:100',
            'uf' => 'nullable|string|size:2',
        ];
        $rules = function_exists('apply_filters') ? apply_filters('admin.user.update.rules', $baseRules, $request, $user) : $baseRules;
        $validated = $this->validateWithFilters($request, $rules);

        // Apenas administradores podem alterar o flag is_admin; quem não é admin mantém o valor atual
        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'ativo' => $request->boolean('ativo', true),
            'cpf' => $this->normalizarCpf($request->input('cpf')),
            'rg' => $request->input('rg'),
            'rg_orgao_emissor' => $request->input('rg_orgao_emissor'),
            'telefone' => $request->input('telefone'),
            'cargo' => $request->input('cargo'),
            'departamento' => $request->input('departamento'),
            'unidade_administrativa_id' => $request->filled('unidade_administrativa_id') ? (int) $request->input('unidade_administrativa_id') : null,
            'custo_hora_tecnica' => $request->filled('custo_hora_tecnica') ? $request->input('custo_hora_tecnica') : null,
            'cep' => $request->input('cep'),
            'logradouro' => $request->input('logradouro'),
            'numero' => $request->input('numero'),
            'complemento' => $request->input('complemento'),
            'bairro' => $request->input('bairro'),
            'cidade' => $request->input('cidade'),
            'uf' => $request->input('uf') ? strtoupper(substr($request->input('uf'), 0, 2)) : null,
        ];
        $extraData = function_exists('apply_filters') ? apply_filters('admin.user.update.data', [], $request, $user, $validated) : [];
        $user->update(array_merge($updateData, $extraData));

        if (auth()->user()->is_admin) {
            $user->is_admin = $request->boolean('is_admin');
            $user->save();
        }

        if ($request->filled('password')) {
            $user->update(['password' => bcrypt($validated['password'])]);
        }

        $editandoASiMesmo = (int) $user->id === (int) auth()->id();
        if ($editorGestor || ! $editandoASiMesmo) {
            if ($request->has('roles')) {
                $novos = $this->papeisPermitidos((array) $request->roles);
                if (! $editorGestor) {
                    // Preserva o papel admin que a chefia não pode conceder nem retirar
                    $adminId = \App\Models\Role::where('slug', 'admin')->value('id');
                    if ($adminId && $user->roles()->where('roles.id', $adminId)->exists()) {
                        $novos[] = $adminId;
                    }
                }
                $user->roles()->sync($novos);
            } elseif ($editorGestor) {
                $user->roles()->detach();
            }
        }

        // Desativar encerra sessões "manter conectado"
        if (! $user->ativo) {
            $user->forceFill(['remember_token' => \Illuminate\Support\Str::random(60)])->save();
        }

        if (function_exists('do_action')) {
            do_action('admin.user.updated', $user, $request);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário atualizado com sucesso!');
    }

    public function destroy(Request $request, User $user, AuditCancelExcluirService $auditService)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Você não pode excluir seu próprio usuário!');
        }

        $this->protegerGestorGeral($user);

        if (!$auditService->canExcluir(auth()->user(), 'usuario')) {
            abort(403, 'Você não tem permissão para excluir usuários.');
        }

        $this->validateWithFilters($request, [
            'motivo' => 'required|string|min:10',
        ], [], ['motivo' => 'motivo da exclusão']);

        $descricao = $user->name ?? $user->email ?? 'Usuário #' . $user->id;
        $entityId = $user->id;

        $user->delete();

        $auditService->log('excluir', 'usuario', $entityId, $descricao, $request->motivo, $request);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário excluído com sucesso!');
    }

    public function restore($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();
        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário restaurado com sucesso!');
    }

    private function normalizarCpf(?string $cpf): ?string
    {
        if ($cpf === null || $cpf === '') {
            return null;
        }
        $digits = preg_replace('/[^0-9]/', '', $cpf);
        return strlen($digits) === 11 ? $digits : null;
    }
}
