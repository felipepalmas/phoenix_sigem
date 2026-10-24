<?php

/**
 * Componente da aplicação Phoenix - SIGEM
 * Seeder da Frota Municipal, Motoristas Oficiais e Diário de Bordo
 *
 * @license AGPL-3.0
 */

namespace Database\Seeders;

use App\Models\Abastecimento;
use App\Models\Motorista;
use App\Models\UnidadeAdministrativa;
use App\Models\User;
use App\Models\Veiculo;
use App\Models\Viagem;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class FrotaSeeder extends Seeder
{
    public function run(): void
    {
        // Secretarias de lotação
        $semad = UnidadeAdministrativa::where('sigla', 'SEMAD')->first();
        $semosp = UnidadeAdministrativa::where('sigla', 'SEMOSP')->first();
        $sems = UnidadeAdministrativa::where('sigla', 'SEMS')->first();
        $semed = UnidadeAdministrativa::where('sigla', 'SEMED')->first();

        // Usuário admin ou servidor
        $admin = User::first();

        // 1. Cadastrar Motoristas Oficiais da Prefeitura
        $motoristasDados = [
            [
                'nome' => 'Antônio Pereira dos Santos',
                'email' => 'motorista.antonio@municipio.gov.br',
                'cnh_numero' => '94827103841',
                'cnh_categoria' => 'D',
                'cnh_validade' => Carbon::now()->addMonths(18),
                'cursos_obrigatorios' => ['transporte_escolar', 'direcao_defensiva'],
                'status' => 'apto',
            ],
            [
                'nome' => 'Marcos Vinícius Barbosa',
                'email' => 'motorista.marcos@municipio.gov.br',
                'cnh_numero' => '83719204852',
                'cnh_categoria' => 'D',
                'cnh_validade' => Carbon::now()->addMonths(8),
                'cursos_obrigatorios' => ['condutor_emergencia_samu', 'primeiros_socorros'],
                'status' => 'apto',
            ],
            [
                'nome' => 'Cláudio Roberto Silveira',
                'email' => 'motorista.claudio@municipio.gov.br',
                'cnh_numero' => '72619403819',
                'cnh_categoria' => 'E',
                'cnh_validade' => Carbon::now()->addMonths(24),
                'cursos_obrigatorios' => ['cargas_indivisiveis', 'operador_maquinas_pesadas'],
                'status' => 'apto',
            ],
            [
                'nome' => 'Reginaldo Farias Lima',
                'email' => 'motorista.reginaldo@municipio.gov.br',
                'cnh_numero' => '61528394012',
                'cnh_categoria' => 'AB',
                'cnh_validade' => Carbon::now()->addDays(15), // Alerta: CNH vencendo em breve!
                'cursos_obrigatorios' => ['direcao_defensiva'],
                'status' => 'apto',
            ],
        ];

        $motoristasCriados = [];

        foreach ($motoristasDados as $m) {
            $user = User::firstOrCreate(
                ['email' => $m['email']],
                [
                    'name' => $m['nome'],
                    'password' => bcrypt('password'),
                    'cargo' => 'Motorista Oficial Municipal',
                    'departamento' => 'Setor de Transporte e Garagem',
                    'ativo' => true,
                ]
            );

            $mot = Motorista::firstOrCreate(
                ['cnh_numero' => $m['cnh_numero']],
                [
                    'user_id' => $user->id,
                    'cnh_categoria' => $m['cnh_categoria'],
                    'cnh_validade' => $m['cnh_validade'],
                    'cursos_obrigatorios' => $m['cursos_obrigatorios'],
                    'status' => $m['status'],
                    'observacoes' => 'Motorista efetivo do quadro geral de condutores.',
                ]
            );

            $motoristasCriados[] = $mot;
        }

        // 2. Cadastrar Frota de Veículos e Máquinas
        $veiculosDados = [
            [
                'placa' => 'BRA2E19',
                'modelo' => 'Sprinter 415 Ambulância UTI SAMU',
                'marca' => 'Mercedes-Benz',
                'ano_fabricacao' => 2022,
                'ano_modelo' => 2023,
                'tipo' => 'ambulancia',
                'combustivel' => 'diesel_s10',
                'renavam' => '01293847561',
                'chassi' => '9BM906633PN819284',
                'patrimonio_tombamento' => 'PAT-FROTA-001',
                'unidade_administrativa_id' => $sems?->id,
                'km_atual' => 48250,
                'status' => 'disponivel',
                'capacidade_tanque' => 75.00,
                'capacidade_passageiros' => 5,
                'observacoes' => 'Ambulância de Suporte Avançado (USA-01) - Atendimento 24h SAMU.',
            ],
            [
                'placa' => 'MUN4F26',
                'modelo' => 'Micro-ônibus Escolar 31 Lugares',
                'marca' => 'Volksbus / Marcopolo',
                'ano_fabricacao' => 2021,
                'ano_modelo' => 2022,
                'tipo' => 'onibus_escolar',
                'combustivel' => 'diesel_s10',
                'renavam' => '09384716253',
                'chassi' => '9BWDB09W3PR718293',
                'patrimonio_tombamento' => 'PAT-FROTA-002',
                'unidade_administrativa_id' => $semed?->id,
                'km_atual' => 62100,
                'status' => 'em_viagem',
                'capacidade_tanque' => 150.00,
                'capacidade_passageiros' => 31,
                'observacoes' => 'Linha Escolar Rural 04 - Região dos Mananciais e Assentamento.',
            ],
            [
                'placa' => 'OBR8A55',
                'modelo' => 'Caminhão Constellation 26.280 Caçamba Basculante',
                'marca' => 'Volkswagen',
                'ano_fabricacao' => 2020,
                'ano_modelo' => 2020,
                'tipo' => 'caminhao',
                'combustivel' => 'diesel_s10',
                'renavam' => '08273645192',
                'chassi' => '9BWDA26X0LR615243',
                'patrimonio_tombamento' => 'PAT-FROTA-003',
                'unidade_administrativa_id' => $semosp?->id,
                'km_atual' => 93400,
                'status' => 'disponivel',
                'capacidade_tanque' => 275.00,
                'capacidade_passageiros' => 3,
                'observacoes' => 'Transporte de massa asfáltica e cascalho para manutenção de estradas rurais.',
            ],
            [
                'placa' => 'MAQ001B',
                'modelo' => 'Retroescavadeira 3CX 4x4 Cabinada',
                'marca' => 'JCB',
                'ano_fabricacao' => 2023,
                'ano_modelo' => 2023,
                'tipo' => 'maquina_pesada',
                'combustivel' => 'diesel_s10',
                'renavam' => null,
                'chassi' => 'JCB3CX4X4TR827361',
                'patrimonio_tombamento' => 'PAT-FROTA-004',
                'unidade_administrativa_id' => $semosp?->id,
                'km_atual' => 1240, // Horímetro em horas de uso
                'status' => 'disponivel',
                'capacidade_tanque' => 130.00,
                'capacidade_passageiros' => 1,
                'observacoes' => 'Alocada na operação de desassoreamento e limpeza de bueiros e córregos.',
            ],
            [
                'placa' => 'FIS3B12',
                'modelo' => 'Gol 1.0 MPI Flex 5 Portas',
                'marca' => 'Volkswagen',
                'ano_fabricacao' => 2022,
                'ano_modelo' => 2023,
                'tipo' => 'passeio',
                'combustivel' => 'flex',
                'renavam' => '07162534891',
                'chassi' => '9BWAB45U7NT918273',
                'patrimonio_tombamento' => 'PAT-FROTA-005',
                'unidade_administrativa_id' => $semosp?->id,
                'km_atual' => 31200,
                'status' => 'disponivel',
                'capacidade_tanque' => 50.00,
                'capacidade_passageiros' => 5,
                'observacoes' => 'Fiscalização de obras, zeladoria urbana e posturas municipais.',
            ],
            [
                'placa' => 'GAB1A01',
                'modelo' => 'Hilux CD SRV 4x4 Diesel Automática',
                'marca' => 'Toyota',
                'ano_fabricacao' => 2023,
                'ano_modelo' => 2024,
                'tipo' => 'utilitario',
                'combustivel' => 'diesel_s10',
                'renavam' => '06253417892',
                'chassi' => '8AJBA3CD4P1827364',
                'patrimonio_tombamento' => 'PAT-FROTA-006',
                'unidade_administrativa_id' => $semad?->id,
                'km_atual' => 19800,
                'status' => 'disponivel',
                'capacidade_tanque' => 80.00,
                'capacidade_passageiros' => 5,
                'observacoes' => 'Veículo oficial representativo do Gabinete do Prefeito para vistorias no interior.',
            ],
        ];

        $veiculosCriados = [];

        foreach ($veiculosDados as $v) {
            $veic = Veiculo::firstOrCreate(
                ['placa' => $v['placa']],
                $v
            );
            $veiculosCriados[] = $veic;
        }

        // 3. Registrar Viagens no Diário de Bordo Digital
        if (!empty($veiculosCriados) && !empty($motoristasCriados)) {
            // Viagem 1: Ambulância (Concluída - TFD para Capital)
            Viagem::firstOrCreate(
                ['numero' => 'VIA-2026/00001'],
                [
                    'veiculo_id' => $veiculosCriados[0]->id,
                    'motorista_id' => $motoristasCriados[1]->id, // Marcos (SAMU)
                    'solicitante_user_id' => $admin?->id,
                    'unidade_administrativa_id' => $sems?->id,
                    'data_saida' => Carbon::now()->subDays(2)->setHour(6)->setMinute(30),
                    'data_retorno' => Carbon::now()->subDays(2)->setHour(18)->setMinute(45),
                    'km_saida' => 47800,
                    'km_retorno' => 48250,
                    'km_percorrido' => 450,
                    'destino' => 'Hospital Regional de Referência (Capital)',
                    'finalidade' => 'Transporte Fora de Domicílio (TFD) para tratamento dialítico e cardiológico de paciente do município.',
                    'situacao' => 'concluida',
                    'checklist_saida' => ['pneus' => 'ok', 'oleo' => 'ok', 'agua' => 'ok', 'oxigenio' => 'completo'],
                    'checklist_retorno' => ['pneus' => 'ok', 'veiculo_higienizado' => 'sim'],
                    'ocorrencias' => 'Viagem realizada sem nenhuma intercorrência mecânica ou de trânsito.',
                ]
            );

            // Viagem 2: Ônibus Escolar (Em Trânsito / Rota Matutina)
            Viagem::firstOrCreate(
                ['numero' => 'VIA-2026/00002'],
                [
                    'veiculo_id' => $veiculosCriados[1]->id,
                    'motorista_id' => $motoristasCriados[0]->id, // Antônio (Escolar)
                    'solicitante_user_id' => $admin?->id,
                    'unidade_administrativa_id' => $semed?->id,
                    'data_saida' => Carbon::now()->subHours(3),
                    'data_retorno' => null,
                    'km_saida' => 62040,
                    'km_retorno' => null,
                    'km_percorrido' => null,
                    'destino' => 'Rota Rural 04 - Comunidade Água Limpa ➜ Escola Polo Municipal',
                    'finalidade' => 'Transporte escolar de 28 estudantes dos ensinos fundamental e médio.',
                    'situacao' => 'em_andamento',
                    'checklist_saida' => ['cintos_seguranca' => 'ok', 'pneus' => 'ok', 'tacografo' => 'operante'],
                ]
            );
        }

        // 4. Registrar Abastecimento
        if (!empty($veiculosCriados) && !empty($motoristasCriados)) {
            Abastecimento::firstOrCreate(
                [
                    'veiculo_id' => $veiculosCriados[0]->id,
                    'data' => Carbon::now()->subDays(2)->setHour(6)->setMinute(45),
                ],
                [
                    'motorista_id' => $motoristasCriados[1]->id,
                    'combustivel' => 'diesel_s10',
                    'litros' => 60.00,
                    'valor_litro' => 5.99,
                    'valor_total' => 359.40,
                    'km_veiculo' => 47800,
                    'km_anterior' => 47350,
                    'consumo_medio_kml' => 7.50,
                    'posto_combustivel' => 'Auto Posto Central - Conveniado PM',
                    'numero_cupom_fiscal' => 'NF-e 0038419',
                    'observacoes' => 'Abastecimento para viagem autorizada de TFD / SAMU.',
                ]
            );
        }
    }
}
