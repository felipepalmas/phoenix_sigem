<?php

/**
 * Componente da aplicação Phoenix - SIGEM
 * Módulo D: Frota Municipal, Diário de Bordo, Motoristas e Gestão de Veículos
 *
 * @license AGPL-3.0
 */

namespace App\Http\Controllers;

use App\Models\Abastecimento;
use App\Models\ManutencaoFrota;
use App\Models\Motorista;
use App\Models\Processo;
use App\Models\UnidadeAdministrativa;
use App\Models\User;
use App\Models\Veiculo;
use App\Models\Viagem;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FrotaController extends Controller
{
    /**
     * Painel Geral da Frota Municipal (Centro de Controle de Veículos e Garagem)
     */
    public function index(Request $request)
    {
        $query = Veiculo::with(['unidadeAdministrativa', 'motoristaResponsavel'])
            ->where('ativo', true)
            ->latest('id');

        if ($request->filled('busca')) {
            $busca = $request->input('busca');
            $query->where(function ($q) use ($busca) {
                $q->where('placa', 'like', "%{$busca}%")
                  ->orWhere('modelo', 'like', "%{$busca}%")
                  ->orWhere('marca', 'like', "%{$busca}%")
                  ->orWhere('patrimonio_tombamento', 'like', "%{$busca}%");
            });
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->input('tipo'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('secretaria_id')) {
            $query->where('unidade_administrativa_id', $request->input('secretaria_id'));
        }

        $veiculos = $query->paginate(12)->withQueryString();

        // KPIs Estratégicos da Frota
        $kpis = [
            'total' => Veiculo::where('ativo', true)->count(),
            'disponiveis' => Veiculo::where('ativo', true)->where('status', 'disponivel')->count(),
            'em_viagem' => Veiculo::where('ativo', true)->where('status', 'em_viagem')->count(),
            'em_manutencao' => Veiculo::where('ativo', true)->whereIn('status', ['em_manutencao', 'inoperante'])->count(),
        ];

        // Alertas de CNH
        $motoristasCnhAlerta = Motorista::with('user')
            ->where('cnh_validade', '<=', Carbon::now()->addDays(30))
            ->orderBy('cnh_validade')
            ->get();

        // Viagens ativas no momento
        $viagensAtivas = Viagem::with(['veiculo', 'motorista.user', 'unidadeAdministrativa'])
            ->where('situacao', 'em_andamento')
            ->latest('data_saida')
            ->take(6)
            ->get();

        $secretarias = UnidadeAdministrativa::where('ativo', true)->orderBy('sigla')->get();

        return view('frota.index', compact('veiculos', 'kpis', 'motoristasCnhAlerta', 'viagensAtivas', 'secretarias'));
    }

    /**
     * Formulário de Cadastro de Novo Veículo
     */
    public function create()
    {
        $secretarias = UnidadeAdministrativa::where('ativo', true)->orderBy('sigla')->get();
        $motoristas = Motorista::with('user')->where('status', 'apto')->get();

        return view('frota.create', compact('secretarias', 'motoristas'));
    }

    /**
     * Gravação do Novo Veículo
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'placa' => 'required|string|max:10|unique:veiculos,placa',
            'modelo' => 'required|string|max:100',
            'marca' => 'required|string|max:60',
            'ano_fabricacao' => 'required|integer|min:1970|max:2030',
            'ano_modelo' => 'required|integer|min:1970|max:2030',
            'tipo' => 'required|string|max:40',
            'combustivel' => 'required|string|max:30',
            'renavam' => 'nullable|string|max:30',
            'chassi' => 'nullable|string|max:40',
            'patrimonio_tombamento' => 'nullable|string|max:40',
            'unidade_administrativa_id' => 'nullable|exists:unidades_administrativas,id',
            'km_atual' => 'required|integer|min:0',
            'capacidade_tanque' => 'nullable|numeric|min:0',
            'capacidade_passageiros' => 'nullable|integer|min:1',
            'observacoes' => 'nullable|string',
        ]);

        $validated['placa'] = strtoupper(trim($validated['placa']));
        $validated['status'] = 'disponivel';
        $validated['ativo'] = true;

        $veiculo = Veiculo::create($validated);

        return redirect()->route('frota.show', $veiculo)
            ->with('success', "Veículo {$veiculo->placa} ({$veiculo->modelo}) cadastrado com sucesso na frota municipal!");
    }

    /**
     * Ficha do Veículo: Detalhes, Histórico de Viagens e Abastecimentos
     */
    public function show(Veiculo $veiculo)
    {
        $veiculo->load(['unidadeAdministrativa', 'motoristaResponsavel']);

        $viagens = $veiculo->viagens()
            ->with(['motorista.user', 'unidadeAdministrativa'])
            ->latest('data_saida')
            ->take(15)
            ->get();

        $abastecimentos = $veiculo->abastecimentos()
            ->with('motorista.user')
            ->latest('data')
            ->take(10)
            ->get();

        $manutencoes = $veiculo->manutencoes()
            ->with('responsavel')
            ->latest('data_agendamento')
            ->take(10)
            ->get();

        $motoristasAptos = Motorista::with('user')->where('status', 'apto')->get();

        return view('frota.show', compact('veiculo', 'viagens', 'abastecimentos', 'manutencoes', 'motoristasAptos'));
    }

    /**
     * Listagem do Diário de Bordo Digital (Viagens)
     */
    public function viagensIndex(Request $request)
    {
        $query = Viagem::with(['veiculo', 'motorista.user', 'unidadeAdministrativa', 'solicitante'])
            ->latest('data_saida');

        if ($request->filled('busca')) {
            $busca = $request->input('busca');
            $query->where(function ($q) use ($busca) {
                $q->where('numero', 'like', "%{$busca}%")
                  ->orWhere('destino', 'like', "%{$busca}%")
                  ->orWhere('finalidade', 'like', "%{$busca}%")
                  ->orWhereHas('veiculo', function ($v) use ($busca) {
                      $v->where('placa', 'like', "%{$busca}%")
                        ->orWhere('modelo', 'like', "%{$busca}%");
                  });
            });
        }

        if ($request->filled('situacao')) {
            $query->where('situacao', $request->input('situacao'));
        }

        $viagens = $query->paginate(15)->withQueryString();
        $veiculosDisponiveis = Veiculo::where('ativo', true)->where('status', 'disponivel')->orderBy('modelo')->get();
        $motoristasAptos = Motorista::with('user')->where('status', 'apto')->get();

        return view('frota.viagens.index', compact('viagens', 'veiculosDisponiveis', 'motoristasAptos'));
    }

    /**
     * Abertura de Nova Viagem no Diário de Bordo (Saída de Veículo)
     */
    public function viagensStore(Request $request)
    {
        $validated = $request->validate([
            'veiculo_id' => 'required|exists:veiculos,id',
            'motorista_id' => 'required|exists:motoristas,id',
            'destino' => 'required|string|max:200',
            'finalidade' => 'required|string',
            'km_saida' => 'required|integer|min:0',
            'data_saida' => 'nullable|date',
            'unidade_administrativa_id' => 'nullable|exists:unidades_administrativas,id',
        ]);

        $veiculo = Veiculo::findOrFail($validated['veiculo_id']);

        if ($veiculo->status !== 'disponivel') {
            return redirect()->back()
                ->withInput()
                ->with('error', "O veículo {$veiculo->placa} não está disponível para iniciar viagem. Situação atual: {$veiculo->status_formatado}.");
        }

        if ($validated['km_saida'] < $veiculo->km_atual) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Hodômetro de saída ({$validated['km_saida']} km) não pode ser inferior ao hodômetro atual registrado do veículo ({$veiculo->km_atual} km).");
        }

        $motorista = Motorista::findOrFail($validated['motorista_id']);

        if ($motorista->status !== 'apto') {
            return redirect()->back()
                ->withInput()
                ->with('error', "O motorista selecionado não está apto para conduzir (Situação: {$motorista->status_formatado}).");
        }

        if ($motorista->isCnhVencida()) {
            return redirect()->back()
                ->withInput()
                ->with('error', "O motorista selecionado está com a CNH vencida ({$motorista->cnh_validade->format('d/m/Y')}). Registro de saída bloqueado.");
        }

        $viagem = \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $veiculo, $request) {
            $novaViagem = Viagem::create([
                'numero' => Viagem::gerarProximoNumero(),
                'veiculo_id' => $veiculo->id,
                'motorista_id' => $validated['motorista_id'],
                'solicitante_user_id' => auth()->id(),
                'unidade_administrativa_id' => $validated['unidade_administrativa_id'] ?? $veiculo->unidade_administrativa_id,
                'data_saida' => !empty($validated['data_saida']) ? Carbon::parse($validated['data_saida']) : now(),
                'km_saida' => $validated['km_saida'],
                'destino' => $validated['destino'],
                'finalidade' => $validated['finalidade'],
                'situacao' => 'em_andamento',
                'checklist_saida' => [
                    'pneus' => $request->input('check_pneus', 'ok'),
                    'oleo_agua' => $request->input('check_oleo', 'ok'),
                    'documentos' => $request->input('check_docs', 'ok'),
                ],
            ]);

            // Atualiza status do veículo para 'em_viagem'
            $veiculo->update([
                'status' => 'em_viagem',
                'km_atual' => max($veiculo->km_atual, $validated['km_saida']),
            ]);

            return $novaViagem;
        });

        return redirect()->back()->with('success', "Saída registrada com sucesso! Viagem {$viagem->numero} em andamento.");
    }

    /**
     * Encerramento da Viagem (Retorno à Garagem Municipal)
     */
    public function viagensRetorno(Request $request, Viagem $viagem)
    {
        if ($viagem->situacao !== 'em_andamento') {
            return redirect()->back()->with('error', "A viagem {$viagem->numero} não pode ser concluída pois se encontra no status '{$viagem->situacao_formatada}'.");
        }

        $validated = $request->validate([
            'km_retorno' => "required|integer|min:{$viagem->km_saida}",
            'ocorrencias' => 'nullable|string',
        ]);

        try {
            $viagem->registrarRetorno(
                $validated['km_retorno'],
                $validated['ocorrencias'] ?? null,
                [
                    'retorno_pneus' => $request->input('check_retorno_pneus', 'ok'),
                    'veiculo_limpo' => $request->input('check_limpo', 'ok'),
                ]
            );
        } catch (\DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', "Viagem {$viagem->numero} concluída! Veículo {$viagem->veiculo->placa} disponível na garagem.");
    }

    /**
     * Registro de Abastecimento
     */
    public function abastecimentosStore(Request $request)
    {
        $validated = $request->validate([
            'veiculo_id' => 'required|exists:veiculos,id',
            'motorista_id' => 'nullable|exists:motoristas,id',
            'data' => 'required|date',
            'combustivel' => 'required|string|max:30',
            'litros' => 'required|numeric|min:0.1',
            'valor_litro' => 'required|numeric|min:0.1',
            'km_veiculo' => 'required|integer|min:0',
            'posto_combustivel' => 'required|string|max:100',
            'numero_cupom_fiscal' => 'nullable|string|max:50',
            'observacoes' => 'nullable|string',
        ]);

        $veiculo = Veiculo::findOrFail($validated['veiculo_id']);

        if ($veiculo->capacidade_tanque && $validated['litros'] > $veiculo->capacidade_tanque) {
            return redirect()->back()
                ->withInput()
                ->with('error', "A quantidade de litros informada ({$validated['litros']} L) excede a capacidade máxima do tanque do veículo ({$veiculo->capacidade_tanque} L). Abastecimento bloqueado por suspeita de inconformidade.");
        }

        if ($validated['km_veiculo'] < $veiculo->km_atual) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Hodômetro informado ({$validated['km_veiculo']} km) não pode ser inferior ao hodômetro atual registrado do veículo ({$veiculo->km_atual} km).");
        }

        $ultimoAbastecimento = $veiculo->abastecimentos()->latest('data')->first();
        
        $kmAnterior = $ultimoAbastecimento ? $ultimoAbastecimento->km_veiculo : null;
        $consumoMedio = null;

        if ($kmAnterior && $validated['km_veiculo'] > $kmAnterior && $validated['litros'] > 0) {
            $consumoMedio = round(($validated['km_veiculo'] - $kmAnterior) / $validated['litros'], 2);
        }

        $valorTotal = round($validated['litros'] * $validated['valor_litro'], 2);

        \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $veiculo, $valorTotal, $kmAnterior, $consumoMedio) {
            Abastecimento::create([
                'veiculo_id' => $veiculo->id,
                'motorista_id' => $validated['motorista_id'] ?? null,
                'data' => Carbon::parse($validated['data']),
                'combustivel' => $validated['combustivel'],
                'litros' => $validated['litros'],
                'valor_litro' => $validated['valor_litro'],
                'valor_total' => $valorTotal,
                'km_veiculo' => $validated['km_veiculo'],
                'km_anterior' => $kmAnterior,
                'consumo_medio_kml' => $consumoMedio,
                'posto_combustivel' => $validated['posto_combustivel'],
                'numero_cupom_fiscal' => $validated['numero_cupom_fiscal'] ?? null,
                'observacoes' => $validated['observacoes'] ?? null,
            ]);

            // Atualiza km do veículo se for maior
            if ($validated['km_veiculo'] > $veiculo->km_atual) {
                $veiculo->update(['km_atual' => $validated['km_veiculo']]);
            }
        });

        return redirect()->back()->with('success', 'Abastecimento registrado com sucesso! Consumo médio calculado: ' . ($consumoMedio ? "{$consumoMedio} km/l" : 'Primeiro registro'));
    }

    /**
     * Gestão de Motoristas Oficiais
     */
    public function motoristasIndex()
    {
        $motoristas = Motorista::with('user')->latest('id')->paginate(15);
        $usuariosDisponiveis = User::whereDoesntHave('motorista')
            ->where('ativo', true)
            ->orderBy('name')
            ->get();

        return view('frota.motoristas.index', compact('motoristas', 'usuariosDisponiveis'));
    }

    /**
     * Cadastro de Novo Motorista Oficial
     */
    public function motoristasStore(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id|unique:motoristas,user_id',
            'cnh_numero' => 'required|string|max:30|unique:motoristas,cnh_numero',
            'cnh_categoria' => 'required|string|max:10',
            'cnh_validade' => 'required|date',
            'cursos_obrigatorios' => 'nullable|array',
            'observacoes' => 'nullable|string',
        ]);

        $validated['status'] = 'apto';

        Motorista::create($validated);

        return redirect()->back()->with('success', 'Motorista oficial credenciado com sucesso no sistema!');
    }
}
