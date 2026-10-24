<?php

/**
 * Componente da aplicação Phoenix - SIGEM
 * Módulo B: Zeladoria Urbana e Serviços Públicos Municipais
 *
 * @license AGPL-3.0
 */

namespace App\Http\Controllers;

use App\Models\CategoriaZeladoria;
use App\Models\Cliente;
use App\Models\OrdemServico;
use App\Models\Processo;
use App\Models\StatusOs;
use App\Models\UnidadeAdministrativa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ZeladoriaController extends Controller
{
    public function index(Request $request)
    {
        $query = OrdemServico::with(['cliente', 'categoriaZeladoria', 'secretariaResponsavel'])
            ->where(function ($q) {
                $q->whereNotNull('categoria_zeladoria_id')
                  ->orWhereNotNull('logradouro')
                  ->orWhere('tipo_os', 'Zeladoria Urbana');
            })
            ->latest('id');

        if ($request->filled('busca')) {
            $busca = $request->input('busca');
            $query->where(function ($q) use ($busca) {
                $q->where('codigo_interno', 'like', "%{$busca}%")
                  ->orWhere('logradouro', 'like', "%{$busca}%")
                  ->orWhere('bairro', 'like', "%{$busca}%")
                  ->orWhere('relato_cliente', 'like', "%{$busca}%")
                  ->orWhereHas('cliente', function ($cli) use ($busca) {
                      $cli->where('nome', 'like', "%{$busca}%");
                  });
            });
        }

        if ($request->filled('categoria_id')) {
            $query->where('categoria_zeladoria_id', $request->input('categoria_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('bairro')) {
            $query->where('bairro', 'like', '%' . $request->input('bairro') . '%');
        }

        $ordens = $query->paginate(15)->withQueryString();
        $categorias = CategoriaZeladoria::where('ativo', true)->orderBy('ordem')->get();
        $statusLista = StatusOs::where('ativo', true)->orderBy('ordem')->get();

        // Dados georreferenciados para renderizar pins no mapa geral
        $pontosMapa = OrdemServico::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('latitude', '!=', 0)
            ->where(function ($q) {
                $q->whereNotNull('categoria_zeladoria_id')
                  ->orWhereNotNull('logradouro')
                  ->orWhere('tipo_os', 'Zeladoria Urbana');
            })
            ->with(['categoriaZeladoria'])
            ->latest('id')
            ->limit(100)
            ->get()
            ->map(function ($os) {
                return [
                    'id' => $os->id,
                    'codigo' => $os->codigo_interno ?: 'OS #' . $os->id,
                    'categoria' => $os->categoriaZeladoria ? $os->categoriaZeladoria->nome : 'Zeladoria',
                    'logradouro' => $os->endereco_completo,
                    'status' => $os->status,
                    'lat' => (float) $os->latitude,
                    'lng' => (float) $os->longitude,
                    'url' => route('zeladoria.show', $os),
                ];
            });

        // Contadores operacionais
        $baseOs = OrdemServico::where(function ($q) {
            $q->whereNotNull('categoria_zeladoria_id')
              ->orWhereNotNull('logradouro')
              ->orWhere('tipo_os', 'Zeladoria Urbana');
        });

        $totalAbertas = (clone $baseOs)->where(function ($q) {
            $q->where('status', 'like', '%Aberta%')
              ->orWhere('status', 'like', '%Registrada%')
              ->orWhere('status', 'like', '%Triagem%');
        })->count();

        $totalExecucao = (clone $baseOs)->where('status', 'like', '%Execução%')->count();
        $totalConcluidas = (clone $baseOs)->where(function ($q) {
            $q->where('status', 'like', '%Concluída%')
              ->orWhere('status', 'like', '%Encerrada%')
              ->orWhere('status', 'like', '%Encerrado%');
        })->count();
        $totalUrgentes = (clone $baseOs)->whereIn('prioridade', ['urgente', 'emergencial'])->count();

        return erp_view('zeladoria.index', [
            'title' => 'Zeladoria Urbana & Serviços Públicos',
            'ordens' => $ordens,
            'categorias' => $categorias,
            'statusLista' => $statusLista,
            'pontosMapa' => $pontosMapa,
            'totalAbertas' => $totalAbertas,
            'totalExecucao' => $totalExecucao,
            'totalConcluidas' => $totalConcluidas,
            'totalUrgentes' => $totalUrgentes,
        ]);
    }

    public function create()
    {
        $categorias = CategoriaZeladoria::where('ativo', true)->orderBy('ordem')->get();
        $secretarias = UnidadeAdministrativa::where('ativo', true)->orderBy('ordem')->get();
        $cidadaos = Cliente::where('ativo', true)->orderBy('nome')->limit(100)->get();
        $processos = Processo::where('status', 'tramitando')->latest('id')->limit(50)->get();

        return erp_view('zeladoria.create', [
            'title' => 'Nova Demanda de Zeladoria Urbana',
            'categorias' => $categorias,
            'secretarias' => $secretarias,
            'cidadaos' => $cidadaos,
            'processos' => $processos,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'categoria_zeladoria_id' => 'required|exists:categorias_zeladoria,id',
            'secretaria_responsavel_id' => 'nullable|exists:unidades_administrativas,id',
            'processo_id' => 'nullable|exists:processos,id',
            'prioridade' => 'required|in:baixa,normal,urgente,emergencial',
            'origem_demanda' => 'required|string|max:50',
            'logradouro' => 'required|string|max:255',
            'numero_logradouro' => 'nullable|string|max:30',
            'bairro' => 'required|string|max:100',
            'referencia_local' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'relato_cliente' => 'required|string|min:5',
            'foto_antes' => 'nullable|image|max:15360', // até 15MB
        ]);

        $categoria = CategoriaZeladoria::find($validated['categoria_zeladoria_id']);

        // Se secretaria não informada, herda da categoria
        $secretariaId = $validated['secretaria_responsavel_id'] ?? ($categoria ? $categoria->secretaria_padrao_id : null);

        // Upload de foto antes (comprovação inicial)
        $caminhoFotoAntes = null;
        if ($request->hasFile('foto_antes') && $request->file('foto_antes')->isValid()) {
            $caminhoFotoAntes = $request->file('foto_antes')->store('zeladoria/' . date('Y/m'), 'public');
        }

        // Gerar código sequencial interno (ex: ZEL-2026/00001)
        $ano = (int) date('Y');
        $ultimoSeq = OrdemServico::whereYear('created_at', $ano)->max('numero_sequencial') ?? 0;
        $proximoSeq = $ultimoSeq + 1;
        $codigoInterno = sprintf('ZEL-%d/%05d', $ano, $proximoSeq);

        $prazoDias = $categoria ? $categoria->prazo_padrao_dias : 7;
        $previstaConclusao = now()->addDays($prazoDias);

        $os = OrdemServico::create([
            'numero_sequencial' => $proximoSeq,
            'codigo_interno' => $codigoInterno,
            'cliente_id' => $validated['cliente_id'],
            'categoria_zeladoria_id' => $validated['categoria_zeladoria_id'],
            'secretaria_responsavel_id' => $secretariaId,
            'processo_id' => $validated['processo_id'] ?? null,
            'prioridade' => $validated['prioridade'],
            'origem_demanda' => $validated['origem_demanda'],
            'status' => 'Registrada / Triagem',
            'tipo_os' => 'Zeladoria Urbana',
            'data_abertura' => now(),
            'prevista_inicio' => now(),
            'prevista_conclusao' => $previstaConclusao,
            'logradouro' => $validated['logradouro'],
            'numero_logradouro' => $validated['numero_logradouro'] ?? null,
            'bairro' => $validated['bairro'],
            'referencia_local' => $validated['referencia_local'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'foto_antes' => $caminhoFotoAntes,
            'relato_cliente' => $validated['relato_cliente'],
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('zeladoria.show', $os)
            ->with('success', "Demanda de Zeladoria nº {$os->codigo_interno} registrada com sucesso!");
    }

    public function show(OrdemServico $ordemServico)
    {
        $ordemServico->load([
            'cliente',
            'categoriaZeladoria',
            'secretariaResponsavel',
            'processo',
            'servicos',
            'produtos',
            'tecnicos.usuario',
        ]);

        return erp_view('zeladoria.show', [
            'title' => "Demanda de Zeladoria " . ($ordemServico->codigo_interno ?: '#' . $ordemServico->id),
            'os' => $ordemServico,
        ]);
    }

    /**
     * Conclusão da Demanda com comprovação fotográfica (Foto Depois) e Atesto
     */
    public function concluir(Request $request, OrdemServico $ordemServico)
    {
        $validated = $request->validate([
            'foto_depois' => 'required|image|max:15360',
            'servicos_realizados' => 'required|string|min:5',
            'atesto_responsavel' => 'required|string|max:150',
            'atesto_cargo' => 'nullable|string|max:100',
        ]);

        $caminhoFotoDepois = null;
        if ($request->hasFile('foto_depois') && $request->file('foto_depois')->isValid()) {
            $caminhoFotoDepois = $request->file('foto_depois')->store('zeladoria/' . date('Y/m'), 'public');
        }

        $ordemServico->update([
            'status' => 'Concluída / Executada',
            'real_conclusao' => now(),
            'servicos_realizados' => $validated['servicos_realizados'],
            'foto_depois' => $caminhoFotoDepois,
            'atesto_responsavel' => $validated['atesto_responsavel'],
            'atesto_cargo' => $validated['atesto_cargo'] ?? 'Fiscal de Obras / Serviços',
            'atesto_em' => now(),
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('zeladoria.show', $ordemServico)
            ->with('success', 'Demanda de zeladoria concluída com sucesso com comprovação fotográfica e atesto!');
    }
}
