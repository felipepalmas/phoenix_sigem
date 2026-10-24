<?php

namespace Database\Seeders;

use App\Models\CategoriaZeladoria;
use App\Models\Cliente;
use App\Models\OrdemServico;
use App\Models\Processo;
use App\Models\Tramitacao;
use App\Models\UnidadeAdministrativa;
use App\Models\User;
use Illuminate\Database\Seeder;

class MunicipalDashboardSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $adminId = $admin ? $admin->id : 1;

        $cidadao = Cliente::first();
        $cidadaoId = $cidadao ? $cidadao->id : 1;

        $semosp = UnidadeAdministrativa::where('sigla', 'SEMOSP')->first();
        $sefaz  = UnidadeAdministrativa::where('sigla', 'SEFAZ')->first();
        $semad  = UnidadeAdministrativa::where('sigla', 'SEMAD')->first();
        $sems   = UnidadeAdministrativa::where('sigla', 'SEMS')->first();
        $semma  = UnidadeAdministrativa::where('sigla', 'SEMMA')->first();
        $progem = UnidadeAdministrativa::where('sigla', 'PROGEM')->first();

        // 1. Processos Administrativos Adicionais para enriquecer o painel
        $processosData = [
            [
                'assunto' => 'Solicitação de Alvará Sanitário para Estabelecimento Comercial',
                'descricao' => 'Requerimento formal de vistoria e emissão de alvará sanitário para panificadora central.',
                'unidade' => $sems,
                'status' => 'tramitando',
                'prioridade' => 'normal',
                'sigilo' => 'publico',
            ],
            [
                'assunto' => 'Licenciamento Ambiental para Drenagem e Pavimentação no Bairro Primavera',
                'descricao' => 'Autos de análise de impacto ambiental para obras de contenção de encosta e águas pluviais.',
                'unidade' => $semma,
                'status' => 'tramitando',
                'prioridade' => 'urgente',
                'sigilo' => 'publico',
            ],
            [
                'assunto' => 'Parecer Jurídico sobre Minuta de Termo de Cooperação Técnica',
                'descricao' => 'Encaminhamento à Procuradoria Geral para análise de legalidade de minuta de convênio estadual.',
                'unidade' => $progem,
                'status' => 'tramitando',
                'prioridade' => 'urgente',
                'sigilo' => 'restrito',
            ],
            [
                'assunto' => 'Revisão Cadastral e Isenção de IPTU para Imóvel de Entidade Filantrópica',
                'descricao' => 'Processo tributário referente a requerimento de imunidade constitucional de IPTU.',
                'unidade' => $sefaz,
                'status' => 'concluido',
                'prioridade' => 'normal',
                'sigilo' => 'publico',
            ],
        ];

        foreach ($processosData as $pData) {
            $existente = Processo::where('assunto', $pData['assunto'])->first();
            if ($existente) {
                continue;
            }

            $num = Processo::gerarProximoNumero();

            $proc = Processo::create([
                'numero'                 => $num['numero'],
                'ano'                    => $num['ano'],
                'sequencial'             => $num['sequencial'],
                'assunto'                => $pData['assunto'],
                'interessado_id'         => $cidadaoId,
                'descricao'              => $pData['descricao'],
                'unidade_atual_id'       => $pData['unidade']?->id ?: 1,
                'created_by'             => $adminId,
                'status'                 => $pData['status'],
                'prioridade'             => $pData['prioridade'],
                'sigilo'                 => $pData['sigilo'],
            ]);

            // Criar uma tramitação inicial se for novo
            if ($proc && $pData['status'] === 'tramitando') {
                Tramitacao::create([
                    'processo_id'            => $proc->id,
                    'unidade_origem_id'      => $semad?->id ?: 1,
                    'unidade_destino_id'     => $pData['unidade']?->id ?: 1,
                    'usuario_envio_id'       => $adminId,
                    'usuario_recebimento_id' => $adminId,
                    'despacho'               => 'Encaminhado para manifestação técnica e providências cabíveis.',
                    'parecer_tipo'           => 'informativo',
                    'status'                 => 'recebido',
                    'data_envio'             => now()->subHours(rand(1, 24)),
                    'data_recebimento'       => now()->subHours(rand(0, 5)),
                    'created_by'             => $adminId,
                ]);
            }
        }

        // 2. Ocorrências de Zeladoria Georreferenciadas na Cidade
        $categorias = CategoriaZeladoria::all()->keyBy('slug');

        $ocorrenciasData = [
            [
                'codigo' => 'ZEL-2026/00112',
                'categoria' => $categorias['iluminacao-publica'] ?? null,
                'relato' => 'Poste com lâmpada apagada há 4 dias próximo à Escola Municipal Monteiro Lobato',
                'logradouro' => 'Rua das Flores, 450',
                'bairro' => 'Jardim América',
                'lat' => -15.798500,
                'lng' => -47.876200,
                'status' => 'Programada',
                'prioridade' => 'normal',
                'origem' => 'telefone_156',
            ],
            [
                'codigo' => 'ZEL-2026/00113',
                'categoria' => $categorias['tapa-buraco-asfalto'] ?? null,
                'relato' => 'Cratera profunda na pista da direita, risco de acidentes com motocicletas',
                'logradouro' => 'Avenida Getúlio Vargas, em frente ao nº 1200',
                'bairro' => 'Distrito Industrial',
                'lat' => -15.805200,
                'lng' => -47.891100,
                'status' => 'Em execução',
                'prioridade' => 'alta',
                'origem' => 'fiscalizacao',
            ],
            [
                'codigo' => 'ZEL-2026/00114',
                'categoria' => $categorias['bueiros-drenagem'] ?? null,
                'relato' => 'Boca de lobo entupida com acúmulo de areia e folhas, causando alagamento na calçada',
                'logradouro' => 'Travessa dos Pássaros, 88',
                'bairro' => 'Vila Nova',
                'lat' => -15.789100,
                'lng' => -47.889900,
                'status' => 'Registrada',
                'prioridade' => 'alta',
                'origem' => 'portal',
            ],
            [
                'codigo' => 'ZEL-2026/00115',
                'categoria' => $categorias['poda-arvores'] ?? null,
                'relato' => 'Galhos de copa densa tocando na fiação de baixa tensão e bloqueando a placa de pare',
                'logradouro' => 'Rua 7 de Setembro, esquina com Rua Rio Branco',
                'bairro' => 'Centro',
                'lat' => -15.791500,
                'lng' => -47.884500,
                'status' => 'Em triagem',
                'prioridade' => 'normal',
                'origem' => 'balcao',
            ],
            [
                'codigo' => 'ZEL-2026/00116',
                'categoria' => $categorias['limpeza-urbana'] ?? null,
                'relato' => 'Ponto de descarte irregular de entulhos em terreno público próximo à praça central',
                'logradouro' => 'Praça dos Pioneiros, s/n',
                'bairro' => 'Bairro São Cristóvão',
                'lat' => -15.801000,
                'lng' => -47.886000,
                'status' => 'Concluída',
                'prioridade' => 'normal',
                'origem' => 'ouvidoria',
                'atesto_responsavel' => 'Carlos Alberto - Encarregado de Limpeza',
                'atesto_cargo' => 'Fiscal de Obras e Posturas',
                'atesto_em' => now()->subDay(),
            ],
        ];

        foreach ($ocorrenciasData as $oc) {
            $os = OrdemServico::firstOrNew(['codigo_interno' => $oc['codigo']]);
            $os->tipo_os = 'chamado_publico';
            $os->status = $oc['status'];
            $os->prioridade = $oc['prioridade'];
            $os->categoria_zeladoria_id = $oc['categoria']?->id;
            $os->secretaria_responsavel_id = $semosp?->id;
            $os->origem_demanda = $oc['origem'];
            $os->relato_cliente = $oc['relato'];
            $os->cliente_id = $cidadaoId;
            $os->logradouro = $oc['logradouro'];
            $os->bairro = $oc['bairro'];
            $os->latitude = $oc['lat'];
            $os->longitude = $oc['lng'];
            $os->data_abertura = now()->subDays(rand(1, 5));
            if (!empty($oc['atesto_responsavel'])) {
                $os->atesto_responsavel = $oc['atesto_responsavel'];
                $os->atesto_cargo = $oc['atesto_cargo'];
                $os->atesto_em = $oc['atesto_em'];
            }
            $os->save();
        }
    }
}
