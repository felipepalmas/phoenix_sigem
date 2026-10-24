<?php

/**
 * Componente da aplicação Phoenix - SIGEM
 * Módulo Administrativo: Gestão da Estrutura Organizacional e Secretarias Municipais
 *
 * @license AGPL-3.0
 */

namespace App\Http\Controllers;

use App\Models\UnidadeAdministrativa;
use App\Models\User;
use Illuminate\Http\Request;

class UnidadeAdministrativaController extends Controller
{
    /**
     * Listagem da Estrutura Organizacional Municipal
     */
    public function index(Request $request)
    {
        $query = UnidadeAdministrativa::with(['parent', 'responsavel'])
            ->withCount(['processosAtuais', 'servidores'])
            ->orderBy('ordem')
            ->orderBy('nome');

        if ($request->filled('busca')) {
            $busca = $request->input('busca');
            $query->where(function ($q) use ($busca) {
                $q->where('nome', 'like', "%{$busca}%")
                  ->orWhere('sigla', 'like', "%{$busca}%")
                  ->orWhere('email', 'like', "%{$busca}%");
            });
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->input('tipo'));
        }

        if ($request->has('ativo') && $request->input('ativo') !== '') {
            $query->where('ativo', (bool) $request->input('ativo'));
        }

        $unidades = $query->paginate(15)->withQueryString();

        return erp_view('secretarias.index', ['title' => 'Secretarias', 'unidades' => $unidades]);
    }

    /**
     * Formulário de Criação de Unidade / Secretaria
     */
    public function create()
    {
        $unidadesPai = UnidadeAdministrativa::where('ativo', true)
            ->whereIn('tipo', ['gabinete', 'secretaria', 'departamento'])
            ->orderBy('sigla')
            ->get();

        $usuarios = User::where('ativo', true)->orderBy('name')->get();

        return erp_view('secretarias.create', ['title' => 'Nova secretaria ou setor', 'unidadesPai' => $unidadesPai, 'usuarios' => $usuarios]);
    }

    /**
     * Gravação da Nova Unidade Administrativa
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:200',
            'sigla' => 'nullable|string|max:30',
            'tipo' => 'required|string|in:gabinete,secretaria,departamento,divisao,setor',
            'parent_id' => 'nullable|exists:unidades_administrativas,id',
            'responsavel_id' => 'nullable|exists:users,id',
            'email' => 'nullable|email|max:150',
            'telefone' => 'nullable|string|max:50',
            'ordem' => 'nullable|integer|min:0',
            'ativo' => 'nullable|boolean',
        ]);

        $validated['sigla'] = !empty($validated['sigla']) ? strtoupper(trim($validated['sigla'])) : null;
        $validated['ativo'] = $request->boolean('ativo', true);
        $validated['ordem'] = $validated['ordem'] ?? 0;
        $validated['created_by'] = auth()->id();

        $unidade = UnidadeAdministrativa::create($validated);

        return redirect()->route('secretarias.index')
            ->with('success', "Unidade Administrativa \"{$unidade->nome_completo}\" cadastrada com sucesso!");
    }

    /**
     * Formulário de Edição da Unidade Administrativa
     */
    public function edit(UnidadeAdministrativa $secretaria)
    {
        $unidadesPai = UnidadeAdministrativa::where('ativo', true)
            ->where('id', '!=', $secretaria->id)
            ->whereIn('tipo', ['gabinete', 'secretaria', 'departamento'])
            ->orderBy('sigla')
            ->get();

        $usuarios = User::where('ativo', true)->orderBy('name')->get();

        return erp_view('secretarias.edit', [
            'title' => 'Editar ' . ($secretaria->sigla ?: $secretaria->nome),
            'unidade' => $secretaria,
            'unidadesPai' => $unidadesPai,
            'usuarios' => $usuarios,
        ]);
    }

    /**
     * Atualização da Unidade Administrativa
     */
    public function update(Request $request, UnidadeAdministrativa $secretaria)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:200',
            'sigla' => 'nullable|string|max:30',
            'tipo' => 'required|string|in:gabinete,secretaria,departamento,divisao,setor',
            'parent_id' => [
                'nullable',
                'exists:unidades_administrativas,id',
                function ($attribute, $value, $fail) use ($secretaria) {
                    if ((int) $value === (int) $secretaria->id) {
                        $fail('Uma unidade administrativa não pode ser subordinada a ela mesma.');
                    }
                },
            ],
            'responsavel_id' => 'nullable|exists:users,id',
            'email' => 'nullable|email|max:150',
            'telefone' => 'nullable|string|max:50',
            'ordem' => 'nullable|integer|min:0',
            'ativo' => 'nullable|boolean',
        ]);

        $validated['sigla'] = !empty($validated['sigla']) ? strtoupper(trim($validated['sigla'])) : null;
        $validated['ativo'] = $request->boolean('ativo', true);
        $validated['ordem'] = $validated['ordem'] ?? 0;

        $secretaria->update($validated);

        return redirect()->route('secretarias.index')
            ->with('success', "Unidade Administrativa \"{$secretaria->nome_completo}\" atualizada com sucesso!");
    }

    /**
     * Exclusão da Unidade Administrativa (com proteção de integridade referencial)
     */
    public function destroy(UnidadeAdministrativa $secretaria)
    {
        if ($secretaria->children()->exists()) {
            return redirect()->back()
                ->with('error', "Não é possível excluir a unidade \"{$secretaria->nome_completo}\" pois ela possui subunidades/setores subordinados. Realoque as subunidades primeiro ou desative o registro.");
        }

        if ($secretaria->processosAtuais()->exists()) {
            return redirect()->back()
                ->with('error', "Não é possível excluir a unidade \"{$secretaria->nome_completo}\" pois existem processos administrativos tramitando nela no momento.");
        }

        $temHistorico = \App\Models\Tramitacao::where('unidade_origem_id', $secretaria->id)->orWhere('unidade_destino_id', $secretaria->id)->exists()
            || \App\Models\RequisicaoMaterial::where('unidade_solicitante_id', $secretaria->id)->exists()
            || $secretaria->servidores()->exists();
        if ($temHistorico) {
            return redirect()->back()
                ->with('error', "A unidade \"{$secretaria->nome_completo}\" tem histórico (tramitações, requisições ou servidores lotados) e não pode ser excluída. Desative-a para que deixe de aparecer nas listas.");
        }

        $nome = $secretaria->nome_completo;
        try {
            $secretaria->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()
                ->with('error', "A unidade \"{$nome}\" está vinculada a outros registros e não pode ser excluída. Desative-a em vez de excluir.");
        }

        return redirect()->route('secretarias.index')
            ->with('success', "Unidade Administrativa \"{$nome}\" excluída com sucesso.");
    }
}
