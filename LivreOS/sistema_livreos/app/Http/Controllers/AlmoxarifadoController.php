<?php

/**
 * Componente da aplicação Phoenix - SIGEM
 *
 * @author    viniciusvams / Phoenix - SIGEM
 * @copyright 2024-2026 LivreOS / Phoenix
 * @license   https://www.gnu.org/licenses/agpl-3.0.txt AGPL-3.0
 */

namespace App\Http\Controllers;

use App\Models\Deposito;
use App\Models\MovimentacaoEstoque;
use App\Models\Processo;
use App\Models\Produto;
use App\Models\RequisicaoMaterial;
use App\Models\RequisicaoMaterialItem;
use App\Models\UnidadeAdministrativa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlmoxarifadoController extends Controller
{
    /**
     * Listagem de Requisições de Materiais
     */
    public function indexRequisicoes(Request $request)
    {
        $query = RequisicaoMaterial::with(['unidadeSolicitante', 'almoxarifado', 'solicitante', 'itens.produto'])
            ->latest('id');

        if ($request->filled('situacao')) {
            $query->where('situacao', $request->input('situacao'));
        }

        if ($request->filled('unidade_id')) {
            $query->where('unidade_solicitante_id', $request->input('unidade_id'));
        }

        if ($request->filled('almoxarifado_id')) {
            $query->where('almoxarifado_id', $request->input('almoxarifado_id'));
        }

        if ($request->filled('busca')) {
            $busca = $request->input('busca');
            $query->where(function ($q) use ($busca) {
                $q->where('numero', 'like', "%{$busca}%")
                  ->orWhere('justificativa', 'like', "%{$busca}%")
                  ->orWhereHas('solicitante', function ($sub) use ($busca) {
                      $sub->where('name', 'like', "%{$busca}%");
                  });
            });
        }

        $requisicoes = $query->paginate(15)->withQueryString();

        $stats = [
            'total'               => RequisicaoMaterial::count(),
            'pendente_aprovacao'  => RequisicaoMaterial::where('situacao', 'pendente_aprovacao')->count(),
            'aprovadas'           => RequisicaoMaterial::where('situacao', 'aprovada')->count(),
            'atendidas'           => RequisicaoMaterial::whereIn('situacao', ['atendida', 'concluida'])->count(),
            'materiais_criticos'  => Produto::whereRaw('estoque_quantidade <= estoque_minimo')->where('estoque_minimo', '>', 0)->count(),
        ];

        $unidades = UnidadeAdministrativa::where('ativo', true)->orderBy('nome')->get();
        $almoxarifados = Deposito::where('ativo', true)->orderBy('nome')->get();

        return view('almoxarifado.requisicoes.index', compact('requisicoes', 'stats', 'unidades', 'almoxarifados'));
    }

    /**
     * Tela de Nova Requisição de Materiais
     */
    public function createRequisicao()
    {
        $unidades = UnidadeAdministrativa::where('ativo', true)->orderBy('nome')->get();
        $almoxarifados = Deposito::where('ativo', true)->orderBy('nome')->get();
        $materiais = Produto::whereNull('deleted_at')
            ->orderBy('nome')
            ->get(['id', 'nome', 'codigo_sku', 'unidade', 'estoque_quantidade', 'estoque_minimo', 'estoque_deposito_id']);

        $processos = Processo::whereNotIn('status', ['concluido', 'arquivado'])
            ->orderBy('id', 'desc')
            ->limit(30)
            ->get(['id', 'numero', 'assunto']);

        return view('almoxarifado.requisicoes.create', compact('unidades', 'almoxarifados', 'materiais', 'processos'));
    }

    /**
     * Gravação da Nova Requisição de Materiais
     */
    public function storeRequisicao(Request $request)
    {
        $validated = $request->validate([
            'unidade_solicitante_id' => 'required|exists:unidades_administrativas,id',
            'almoxarifado_id'        => 'required|exists:depositos,id',
            'prioridade'             => 'required|in:normal,urgente',
            'justificativa'          => 'required|string|min:5',
            'processo_id'            => 'nullable|exists:processos,id',
            'itens'                  => 'required|array|min:1',
            'itens.*.produto_id'     => 'required|exists:produtos,id',
            'itens.*.quantidade'     => 'required|numeric|min:0.01',
        ], [
            'itens.required'         => 'Adicione pelo menos um item à requisição de materiais.',
            'itens.*.quantidade.min' => 'A quantidade solicitada deve ser maior que zero.',
        ]);

        $requisicao = DB::transaction(function () use ($validated, $request) {
            $user = auth()->user();

            $req = RequisicaoMaterial::create([
                'unidade_solicitante_id' => $validated['unidade_solicitante_id'],
                'almoxarifado_id'        => $validated['almoxarifado_id'],
                'solicitante_user_id'    => $user->id,
                'situacao'               => 'pendente_aprovacao',
                'prioridade'             => $validated['prioridade'],
                'justificativa'          => $validated['justificativa'],
                'processo_id'            => $validated['processo_id'] ?? null,
            ]);

            foreach ($validated['itens'] as $itemData) {
                if (!empty($itemData['produto_id']) && !empty($itemData['quantidade'])) {
                    RequisicaoMaterialItem::create([
                        'requisicao_id'         => $req->id,
                        'produto_id'            => $itemData['produto_id'],
                        'quantidade_solicitada' => $itemData['quantidade'],
                        'quantidade_atendida'   => 0,
                    ]);
                }
            }

            return $req;
        });

        return redirect()->route('almoxarifado.requisicoes.show', $requisicao)
            ->with('success', "Requisição {$requisicao->numero} cadastrada com sucesso e enviada para aprovação.");
    }

    /**
     * Detalhes da Requisição
     */
    public function showRequisicao(RequisicaoMaterial $requisicao)
    {
        $requisicao->load([
            'unidadeSolicitante',
            'almoxarifado',
            'solicitante',
            'aprovadoPor',
            'atendidoPor',
            'recebidoPor',
            'processo',
            'itens.produto',
        ]);

        return view('almoxarifado.requisicoes.show', compact('requisicao'));
    }

    /**
     * Aprovação da Requisição pela chefia da unidade
     */
    public function aprovarRequisicao(Request $request, RequisicaoMaterial $requisicao)
    {
        $request->validate([
            'parecer' => 'nullable|string|max:500',
        ]);

        try {
            $requisicao->aprovar(auth()->user(), $request->input('parecer'));
            return redirect()->back()->with('success', "Requisição {$requisicao->numero} aprovada com sucesso e encaminhada ao Almoxarifado para separação.");
        } catch (\DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Recusa da Requisição
     */
    public function recusarRequisicao(Request $request, RequisicaoMaterial $requisicao)
    {
        $request->validate([
            'motivo' => 'required|string|min:5|max:500',
        ]);

        try {
            $requisicao->recusar(auth()->user(), $request->input('motivo'));
            return redirect()->back()->with('warning', "Requisição {$requisicao->numero} foi recusada.");
        } catch (\DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Atendimento e Baixa de Estoque pelo Almoxarife
     */
    public function atenderRequisicao(Request $request, RequisicaoMaterial $requisicao)
    {
        $request->validate([
            'itens'       => 'required|array',
            'observacoes' => 'nullable|string|max:500',
        ]);

        $itensAtendidos = [];
        foreach ($request->input('itens', []) as $itemId => $qtd) {
            $itensAtendidos[$itemId] = (float) $qtd;
        }

        try {
            $requisicao->atender(auth()->user(), $itensAtendidos, $request->input('observacoes'));
            return redirect()->back()->with('success', "Requisição {$requisicao->numero} atendida! Materiais baixados do estoque e prontos para retirada.");
        } catch (\DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Confirmação de Recebimento pelo Solicitante
     */
    public function confirmarRecebimento(RequisicaoMaterial $requisicao)
    {
        try {
            $requisicao->confirmarRecebimento(auth()->user());
            return redirect()->back()->with('success', "Recebimento dos materiais confirmado com sucesso! Requisição concluída.");
        } catch (\DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Movimentações de Estoque (Kardex Municipal)
     */
    public function indexMovimentacoes(Request $request)
    {
        $query = MovimentacaoEstoque::with(['produto', 'deposito', 'user'])->latest('id');

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->input('tipo'));
        }

        if ($request->filled('deposito_id')) {
            $query->where('deposito_id', $request->input('deposito_id'));
        }

        if ($request->filled('produto_id')) {
            $query->where('produto_id', $request->input('produto_id'));
        }

        if ($request->filled('busca')) {
            $busca = $request->input('busca');
            $query->where(function ($q) use ($busca) {
                $q->where('documento_ref', 'like', "%{$busca}%")
                  ->orWhere('justificativa', 'like', "%{$busca}%")
                  ->orWhereHas('produto', function ($sub) use ($busca) {
                      $sub->where('nome', 'like', "%{$busca}%")
                          ->orWhere('codigo_sku', 'like', "%{$busca}%");
                  });
            });
        }

        $movimentacoes = $query->paginate(20)->withQueryString();

        $stats = [
            'total_movimentacoes' => MovimentacaoEstoque::count(),
            'entradas'            => MovimentacaoEstoque::where('tipo', 'entrada')->count(),
            'saidas'              => MovimentacaoEstoque::where('tipo', 'saida')->count(),
            'total_itens'         => Produto::whereNull('deleted_at')->count(),
        ];

        $produtos = Produto::whereNull('deleted_at')->orderBy('nome')->get(['id', 'nome', 'codigo_sku']);
        $almoxarifados = Deposito::where('ativo', true)->orderBy('nome')->get();

        return view('almoxarifado.movimentacoes.index', compact('movimentacoes', 'stats', 'produtos', 'almoxarifados'));
    }

    /**
     * Registro de Entrada de Materiais por Empenho / NF
     */
    public function storeEntrada(Request $request)
    {
        $validated = $request->validate([
            'produto_id'    => 'required|exists:produtos,id',
            'deposito_id'   => 'required|exists:depositos,id',
            'quantidade'    => 'required|numeric|min:0.01',
            'documento_ref' => 'required|string|max:100', // ex: Nota de Empenho nº 450/2026
            'justificativa' => 'nullable|string|max:500',
        ]);

        MovimentacaoEstoque::registrar(
            produtoId: (int) $validated['produto_id'],
            depositoId: (int) $validated['deposito_id'],
            tipo: 'entrada',
            quantidade: (float) $validated['quantidade'],
            origemTipo: 'nota_empenho',
            documentoRef: $validated['documento_ref'],
            userId: auth()->id(),
            justificativa: $validated['justificativa'] ?? 'Entrada por nota fiscal / empenho'
        );

        return redirect()->route('almoxarifado.movimentacoes.index')
            ->with('success', 'Entrada de material registrada com sucesso no Kardex!');
    }
}
