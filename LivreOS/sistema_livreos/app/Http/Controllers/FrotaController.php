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
use Illuminate\Validation\Rule;

class FrotaController extends Controller
{
    /**
     * Quem pode cadastrar veículos e credenciar motoristas.
     */
    private function podeGerenciar(): bool
    {
        $user = auth()->user();

        return $user && ($user->ehGestorGeral() || $user->hasPermission('frota.gerenciar'));
    }

    /**
     * Motoristas com CNH vencida ou vencendo em até 30 dias (alerta da frota).
     */
    private function alertasCnh(): array
    {
        $lista = Motorista::with('user')
            ->whereNotNull('cnh_validade')
            ->where('cnh_validade', '<=', Carbon::today()->addDays(30)->endOfDay())
            ->orderBy('cnh_validade')
            ->get();

        return [
            'vencidas' => $lista->filter(fn (Motorista $m) => $m->isCnhVencida())->values(),
            'vencendo' => $lista->reject(fn (Motorista $m) => $m->isCnhVencida())->values(),
        ];
    }

    /**
     * Veículos da frota: lista densa com recortes por situação.
     */
    public function index(Request $request)
    {
        $base = Veiculo::where('ativo', true);

        $recortes = [
            'todos' => ['Todos', fn ($q) => $q],
            'disponiveis' => ['Disponíveis', fn ($q) => $q->where('status', 'disponivel')],
            'em_viagem' => ['Em viagem', fn ($q) => $q->where('status', 'em_viagem')],
            'manutencao' => ['Em manutenção', fn ($q) => $q->whereIn('status', ['em_manutencao', 'inoperante'])],
            'baixados' => ['Baixados', fn ($q) => $q->where('status', 'baixado')],
        ];
        $recorte = array_key_exists((string) $request->input('recorte'), $recortes) ? $request->input('recorte') : 'todos';

        $abas = [];
        foreach ($recortes as $chave => [$rotulo, $filtro]) {
            $total = $filtro(clone $base)->count();
            if ($chave === 'baixados' && $total === 0) {
                continue;
            }
            $abas[] = [
                'name' => $rotulo,
                'path' => route('frota.index', ['recorte' => $chave]),
                'active' => $chave === $recorte,
                'count' => $total,
            ];
        }

        $query = $recortes[$recorte][1]((clone $base)->with(['unidadeAdministrativa', 'motoristaResponsavel']));

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

        if ($request->filled('secretaria_id')) {
            $query->where('unidade_administrativa_id', $request->input('secretaria_id'));
        }

        $veiculos = $query->orderBy('placa')->paginate(20)->withQueryString();

        return erp_view('frota.index', [
            'title' => 'Frota',
            'veiculos' => $veiculos,
            'abas' => $abas,
            'recorte' => $recorte,
            'alertasCnh' => $this->alertasCnh(),
            'secretarias' => UnidadeAdministrativa::where('ativo', true)->orderBy('sigla')->get(),
            'filtrosAtivos' => collect($request->only(['busca', 'tipo', 'secretaria_id']))->filter()->isNotEmpty(),
            'podeGerenciar' => $this->podeGerenciar(),
        ]);
    }

    /**
     * Formulário de Cadastro de Novo Veículo
     */
    public function create()
    {
        if (! auth()->user()->ehGestorGeral() && ! auth()->user()->hasPermission('frota.gerenciar')) {
            return redirect()->route('frota.index')->with('error', 'Seu perfil não tem permissão para gerenciar a frota (cadastro de veículos e motoristas).');
        }

        $secretarias = UnidadeAdministrativa::where('ativo', true)->orderBy('sigla')->get();
        $motoristas = Motorista::with('user')->where('status', 'apto')->get();

        return erp_view('frota.create', ['title' => 'Novo veículo'] + compact('secretarias', 'motoristas'));
    }

    /**
     * Gravação do Novo Veículo
     */
    public function store(Request $request)
    {
        if (! auth()->user()->ehGestorGeral() && ! auth()->user()->hasPermission('frota.gerenciar')) {
            return redirect()->route('frota.index')->with('error', 'Seu perfil não tem permissão para gerenciar a frota (cadastro de veículos e motoristas).');
        }

        $validated = $request->validate([
            'placa' => 'required|string|max:10|unique:veiculos,placa',
            'modelo' => 'required|string|max:100',
            'marca' => 'required|string|max:60',
            'ano_fabricacao' => 'required|integer|min:1970|max:2030',
            'ano_modelo' => 'required|integer|min:1970|max:2030',
            'tipo' => ['required', 'string', Rule::in(array_keys(Veiculo::TIPOS))],
            'combustivel' => ['required', 'string', Rule::in(array_keys(Veiculo::COMBUSTIVEIS))],
            'renavam' => 'nullable|string|max:30',
            'chassi' => 'nullable|string|max:40',
            'patrimonio_tombamento' => 'nullable|string|max:40',
            'unidade_administrativa_id' => 'nullable|exists:unidades_administrativas,id',
            'km_atual' => 'required|integer|min:0',
            'capacidade_tanque' => 'nullable|numeric|min:0',
            'capacidade_passageiros' => 'nullable|integer|min:1',
            'observacoes' => 'nullable|string',
        ], [
            'placa.unique' => 'Já existe um veículo cadastrado com esta placa.',
            'tipo.in' => 'Selecione um tipo de veículo da lista.',
            'combustivel.in' => 'Selecione um combustível da lista.',
        ]);

        $validated['placa'] = strtoupper(str_replace(['-', ' '], '', trim($validated['placa'])));
        if (! empty($validated['chassi'])) {
            $validated['chassi'] = strtoupper(trim($validated['chassi']));
        }
        $validated['status'] = 'disponivel';
        $validated['ativo'] = true;

        $veiculo = Veiculo::create($validated);

        return redirect()->route('frota.show', $veiculo)
            ->with('success', "Veículo {$veiculo->placa} ({$veiculo->modelo}) cadastrado na frota.");
    }

    /**
     * Ficha do Veículo: Detalhes, Histórico de Viagens, Abastecimentos e Manutenções
     */
    public function show(Veiculo $veiculo)
    {
        $veiculo->load(['unidadeAdministrativa', 'motoristaResponsavel']);

        $viagens = $veiculo->viagens()
            ->with(['motorista.user', 'unidadeAdministrativa'])
            ->latest('data_saida')
            ->take(15)
            ->get();

        $viagemAtiva = $veiculo->viagens()
            ->where('situacao', 'em_andamento')
            ->with('motorista.user')
            ->latest('data_saida')
            ->first();

        $abastecimentos = $veiculo->abastecimentos()
            ->with('motorista.user')
            ->latest('data')
            ->latest('id')
            ->take(10)
            ->get();

        $manutencoes = $veiculo->manutencoes()
            ->with('responsavel')
            ->latest('data_agendamento')
            ->take(10)
            ->get();

        $motoristas = Motorista::with('user')->where('status', 'apto')->get()
            ->sortBy(fn (Motorista $m) => $m->nome)->values();

        return erp_view('frota.show', [
            'title' => 'Veículo ' . $veiculo->placa,
            'veiculo' => $veiculo,
            'viagens' => $viagens,
            'viagemAtiva' => $viagemAtiva,
            'abastecimentos' => $abastecimentos,
            'manutencoes' => $manutencoes,
            'motoristas' => $motoristas,
            'podeSair' => $veiculo->ativo && $veiculo->status === 'disponivel',
            'podeAbastecer' => $veiculo->ativo && $veiculo->status !== 'baixado',
            'totalViagens' => $veiculo->viagens()->count(),
            'totalAbastecimentos' => $veiculo->abastecimentos()->count(),
            'totalManutencoes' => $veiculo->manutencoes()->count(),
        ]);
    }

    /**
     * Listagem do Diário de Bordo Digital (Viagens)
     */
    public function viagensIndex(Request $request)
    {
        $base = Viagem::query();

        $recortes = [
            'em_andamento' => ['Em andamento', fn ($q) => $q->where('situacao', 'em_andamento')],
            'concluidas' => ['Concluídas', fn ($q) => $q->where('situacao', 'concluida')],
            'todas' => ['Todas', fn ($q) => $q],
        ];
        $padrao = (clone $base)->where('situacao', 'em_andamento')->exists() ? 'em_andamento' : 'todas';
        $recorte = array_key_exists((string) $request->input('recorte'), $recortes) ? $request->input('recorte') : $padrao;

        $abas = [];
        foreach ($recortes as $chave => [$rotulo, $filtro]) {
            $abas[] = [
                'name' => $rotulo,
                'path' => route('frota.viagens.index', ['recorte' => $chave]),
                'active' => $chave === $recorte,
                'count' => $filtro(clone $base)->count(),
            ];
        }

        $query = $recortes[$recorte][1]((clone $base)->with(['veiculo', 'motorista.user', 'unidadeAdministrativa', 'solicitante']));

        if ($request->filled('busca')) {
            $busca = $request->input('busca');
            $query->where(function ($q) use ($busca) {
                $q->where('numero', 'like', "%{$busca}%")
                  ->orWhere('destino', 'like', "%{$busca}%")
                  ->orWhere('finalidade', 'like', "%{$busca}%")
                  ->orWhereHas('veiculo', function ($v) use ($busca) {
                      $v->where('placa', 'like', "%{$busca}%")
                        ->orWhere('modelo', 'like', "%{$busca}%");
                  })
                  ->orWhereHas('motorista.user', fn ($u) => $u->where('name', 'like', "%{$busca}%"));
            });
        }

        $viagens = $query
            ->orderByRaw("CASE WHEN situacao = 'em_andamento' THEN 0 ELSE 1 END")
            ->latest('data_saida')
            ->paginate(20)
            ->withQueryString();

        $veiculosDisponiveis = Veiculo::where('ativo', true)->where('status', 'disponivel')->orderBy('placa')->get();
        $motoristas = Motorista::with('user')->where('status', 'apto')->get()
            ->sortBy(fn (Motorista $m) => $m->nome)->values();

        return erp_view('frota.viagens.index', [
            'title' => 'Diário de bordo',
            'viagens' => $viagens,
            'abas' => $abas,
            'recorte' => $recorte,
            'filtrosAtivos' => $request->filled('busca'),
            'veiculosDisponiveis' => $veiculosDisponiveis,
            'motoristas' => $motoristas,
            'secretarias' => UnidadeAdministrativa::where('ativo', true)->orderBy('sigla')->get(),
        ]);
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
                ->withErrors(['veiculo_id' => "O veículo {$veiculo->placa} não está disponível para sair (situação atual: {$veiculo->status_formatado})."]);
        }

        if ($validated['km_saida'] < $veiculo->km_atual) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['km_saida' => "O hodômetro de saída ({$validated['km_saida']} {$veiculo->unidade_medida}) não pode ser menor que o atual do veículo ({$veiculo->hodometro_formatado})."]);
        }

        $motorista = Motorista::findOrFail($validated['motorista_id']);

        if ($motorista->status !== 'apto') {
            return redirect()->back()
                ->withInput()
                ->withErrors(['motorista_id' => "O motorista selecionado não está apto para conduzir (situação: {$motorista->status_formatado})."]);
        }

        if ($motorista->isCnhVencida()) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['motorista_id' => "O motorista selecionado está com a CNH vencida desde {$motorista->cnh_validade->format('d/m/Y')}. Saída não registrada."]);
        }

        $viagem = \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $veiculo, $request) {
            // Trava o veículo: duas saídas simultâneas não podem passar pela checagem de disponibilidade
            $travado = Veiculo::whereKey($veiculo->id)->lockForUpdate()->first();
            if (! $travado || $travado->status !== 'disponivel') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'veiculo_id' => 'O veículo acabou de sair em outra viagem ou não está disponível.',
                ]);
            }

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

        return redirect()->back()->with('success', "Saída registrada: viagem {$viagem->numero} em andamento com o veículo {$veiculo->placa}.");
    }

    /**
     * Encerramento da Viagem (Retorno à Garagem Municipal)
     */
    public function viagensRetorno(Request $request, Viagem $viagem)
    {
        if ($viagem->situacao !== 'em_andamento') {
            return redirect()->back()->with('error', "A viagem {$viagem->numero} não pode ser concluída: situação atual \"{$viagem->situacao_formatada}\".");
        }

        $validated = $request->validate([
            'km_retorno' => "required|integer|min:{$viagem->km_saida}",
            'ocorrencias' => 'nullable|string',
        ], [
            'km_retorno.min' => "O hodômetro de retorno não pode ser menor que o de saída ({$viagem->km_saida}).",
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

        return redirect()->back()->with('success', "Retorno registrado: viagem {$viagem->numero} concluída ({$viagem->km_percorrido} {$viagem->veiculo->unidade_medida} percorridos). Veículo {$viagem->veiculo->placa} disponível.");
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

        $capacidade = (float) $veiculo->capacidade_tanque;
        if ($capacidade > 0 && (float) $validated['litros'] > $capacidade) {
            $litros = number_format((float) $validated['litros'], 2, ',', '.');
            $tanque = number_format($capacidade, 2, ',', '.');

            return redirect()->back()
                ->withInput()
                ->withErrors(['litros' => "A quantidade informada ({$litros} L) é maior que a capacidade do tanque do veículo {$veiculo->placa} ({$tanque} L). Confira o cupom fiscal: se houve abastecimento em galões ou de outro veículo, registre-o separadamente."]);
        }

        if ($validated['km_veiculo'] < $veiculo->km_atual) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['km_veiculo' => "O hodômetro informado ({$validated['km_veiculo']} {$veiculo->unidade_medida}) não pode ser menor que o atual do veículo ({$veiculo->hodometro_formatado})."]);
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

        $totalFmt = number_format($valorTotal, 2, ',', '.');
        $consumoFmt = $consumoMedio ? number_format($consumoMedio, 2, ',', '.') . ' ' . $veiculo->unidade_medida . '/L' : null;

        return redirect()->back()->with('success', "Abastecimento registrado (R$ {$totalFmt})." . ($consumoFmt ? " Consumo desde o último abastecimento: {$consumoFmt}." : ' Primeiro abastecimento do veículo: o consumo será calculado a partir do próximo.'));
    }

    /**
     * Gestão de Motoristas Oficiais
     */
    public function motoristasIndex()
    {
        $motoristas = Motorista::with('user')
            ->withCount(['viagens as viagens_em_andamento_count' => fn ($q) => $q->where('situacao', 'em_andamento')])
            ->orderBy('cnh_validade')
            ->paginate(20);

        $podeGerenciar = $this->podeGerenciar();
        $usuariosDisponiveis = $podeGerenciar
            ? User::whereDoesntHave('motorista')->where('ativo', true)->orderBy('name')->get()
            : collect();

        return erp_view('frota.motoristas.index', [
            'title' => 'Motoristas',
            'motoristas' => $motoristas,
            'usuariosDisponiveis' => $usuariosDisponiveis,
            'alertasCnh' => $this->alertasCnh(),
            'podeGerenciar' => $podeGerenciar,
        ]);
    }

    /**
     * Cadastro de Novo Motorista Oficial
     */
    public function motoristasStore(Request $request)
    {
        if (! auth()->user()->ehGestorGeral() && ! auth()->user()->hasPermission('frota.gerenciar')) {
            return redirect()->route('frota.index')->with('error', 'Seu perfil não tem permissão para gerenciar a frota (cadastro de veículos e motoristas).');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id|unique:motoristas,user_id',
            'cnh_numero' => 'required|string|max:30|unique:motoristas,cnh_numero',
            'cnh_categoria' => 'required|string|max:10',
            'cnh_validade' => 'required|date',
            'cursos_obrigatorios' => 'nullable|array',
            'cursos_obrigatorios.*' => ['string', Rule::in(array_keys(Motorista::CURSOS))],
            'observacoes' => 'nullable|string',
        ]);

        $validated['status'] = 'apto';

        $motorista = Motorista::create($validated);

        return redirect()->back()->with('success', "Motorista {$motorista->load('user')->nome} credenciado.");
    }
}
