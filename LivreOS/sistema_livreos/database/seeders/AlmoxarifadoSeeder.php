<?php

namespace Database\Seeders;

use App\Models\CategoriaProduto;
use App\Models\Deposito;
use App\Models\MovimentacaoEstoque;
use App\Models\Produto;
use App\Models\UnidadeAdministrativa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AlmoxarifadoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $adminId = $admin ? $admin->id : 1;

        // 1. Categorias de Materiais
        $categorias = [
            'Materiais de Expediente e Escritório' => 'expediente',
            'Materiais de Obras e Pavimentação'   => 'obras-pavimentacao',
            'Iluminação Pública e Material Elétrico' => 'iluminacao-eletrico',
            'EPIs e Segurança do Trabalho'         => 'epi-seguranca',
            'Higiene e Limpeza Urbana'             => 'higiene-limpeza',
            'Saúde e Insumos Médicos'              => 'saude-insumos',
        ];

        $catModels = [];
        foreach ($categorias as $nome => $slug) {
            $catModels[$slug] = CategoriaProduto::firstOrCreate(
                ['slug' => $slug],
                ['nome' => $nome, 'ativo' => true]
            );
        }

        // 2. Unidades Municipais
        $semad  = UnidadeAdministrativa::where('sigla', 'SEMAD')->first();
        $semosp = UnidadeAdministrativa::where('sigla', 'SEMOSP')->first();
        $sems   = UnidadeAdministrativa::where('sigla', 'SEMS')->first();
        $semed  = UnidadeAdministrativa::where('sigla', 'SEMED')->first();

        // 3. Almoxarifados Setoriais
        $depositosData = [
            [
                'codigo' => 'ALM-CENTRAL',
                'nome' => 'Almoxarifado Central da Prefeitura',
                'localizacao' => 'Paço Municipal - Térreo Bloco B',
                'unidade_administrativa_id' => $semad?->id,
                'responsavel_user_id' => $adminId,
                'ativo' => true,
            ],
            [
                'codigo' => 'ALM-OBRAS',
                'nome' => 'Almoxarifado Setorial de Obras e Serviços Públicos',
                'localizacao' => 'Pátio de Obras e Máquinas - Distrito Industrial',
                'unidade_administrativa_id' => $semosp?->id,
                'responsavel_user_id' => $adminId,
                'ativo' => true,
            ],
            [
                'codigo' => 'ALM-SAUDE',
                'nome' => 'Almoxarifado Central de Saúde e Farmácia Básica',
                'localizacao' => 'Complexo de Saúde Municipal - Anexo Farmácia',
                'unidade_administrativa_id' => $sems?->id,
                'responsavel_user_id' => $adminId,
                'ativo' => true,
            ],
            [
                'codigo' => 'ALM-EDUC',
                'nome' => 'Almoxarifado Setorial da Educação',
                'localizacao' => 'Sede SEMED - Galpão de Merenda e Suprimentos',
                'unidade_administrativa_id' => $semed?->id,
                'responsavel_user_id' => $adminId,
                'ativo' => true,
            ],
        ];

        $almoxarifados = [];
        foreach ($depositosData as $data) {
            $dep = Deposito::firstOrNew(['codigo' => $data['codigo']]);
            $dep->nome = $data['nome'];
            $dep->localizacao = $data['localizacao'];
            $dep->unidade_administrativa_id = $data['unidade_administrativa_id'];
            $dep->responsavel_user_id = $data['responsavel_user_id'];
            $dep->ativo = true;
            $dep->save();
            $almoxarifados[$data['codigo']] = $dep;
        }

        // 4. Materiais com Estoque Inicial e Kardex
        $materiais = [
            [
                'nome' => 'Lâmpada LED Pública 100W para Braço de Poste',
                'codigo' => 'MAT-ILUM-001',
                'categoria' => 'iluminacao-eletrico',
                'almox' => 'ALM-OBRAS',
                'unidade' => 'UN',
                'qtd' => 150,
                'min' => 30,
                'valor' => 185.00,
            ],
            [
                'nome' => 'Massa Asfáltica a Frio CBUQ para Tapa-Buraco (Saco 25kg)',
                'codigo' => 'MAT-ASF-002',
                'categoria' => 'obras-pavimentacao',
                'almox' => 'ALM-OBRAS',
                'unidade' => 'SC',
                'qtd' => 400,
                'min' => 100,
                'valor' => 42.50,
            ],
            [
                'nome' => 'Resma Papel Sulfite A4 75g (Pacote 500 Folhas)',
                'codigo' => 'MAT-EXP-003',
                'categoria' => 'expediente',
                'almox' => 'ALM-CENTRAL',
                'unidade' => 'PT',
                'qtd' => 300,
                'min' => 50,
                'valor' => 28.90,
            ],
            [
                'nome' => 'Luva de Raspa de Couro Cano Longo (EPI Gari/Obras)',
                'codigo' => 'MAT-EPI-004',
                'categoria' => 'epi-seguranca',
                'almox' => 'ALM-OBRAS',
                'unidade' => 'PR',
                'qtd' => 120,
                'min' => 25,
                'valor' => 19.80,
            ],
            [
                'nome' => 'Tinta para Demarcação Viária e Sinalização Urbana Amarela (Balde 18L)',
                'codigo' => 'MAT-SIN-005',
                'categoria' => 'obras-pavimentacao',
                'almox' => 'ALM-OBRAS',
                'unidade' => 'BD',
                'qtd' => 60,
                'min' => 15,
                'valor' => 275.00,
            ],
            [
                'nome' => 'Vassoura Tipo Gari Reforçada com Cabo Madeira 1,50m',
                'codigo' => 'MAT-LIMP-006',
                'categoria' => 'higiene-limpeza',
                'almox' => 'ALM-CENTRAL',
                'unidade' => 'UN',
                'qtd' => 85,
                'min' => 20,
                'valor' => 34.00,
            ],
        ];

        foreach ($materiais as $m) {
            $cat = $catModels[$m['categoria']] ?? null;
            $dep = $almoxarifados[$m['almox']] ?? null;

            $produto = Produto::firstOrNew(['codigo_sku' => $m['codigo']]);
            $produto->nome = $m['nome'];
            $produto->formato = 'simples';
            $produto->categoria_id = $cat?->id;
            $produto->unidade = $m['unidade'];
            $produto->estoque_minimo = $m['min'];
            $produto->estoque_deposito_id = $dep?->id;
            $produto->estoque_preco_compra_unitario = $m['valor'];
            $produto->estoque_preco_custo_unitario = $m['valor'];
            $produto->preco_venda = $m['valor'];
            $produto->estoque_localizacao = 'Corredor A / Prateleira 01';
            
            $eraNovo = !$produto->exists;
            $produto->estoque_quantidade = $m['qtd'];
            $produto->save();

            // Se produto novo ou sem histórico, lança a carga inicial no Kardex
            if ($eraNovo || MovimentacaoEstoque::where('produto_id', $produto->id)->count() === 0) {
                MovimentacaoEstoque::create([
                    'produto_id'      => $produto->id,
                    'deposito_id'     => $dep?->id ?: 1,
                    'tipo'            => 'entrada',
                    'quantidade'      => $m['qtd'],
                    'saldo_anterior'  => 0,
                    'saldo_posterior' => $m['qtd'],
                    'origem_tipo'     => 'nota_empenho',
                    'documento_ref'   => 'Empenho Inicial ' . date('Y') . '/001',
                    'user_id'         => $adminId,
                    'justificativa'   => 'Carga inicial de saldo de materiais do almoxarifado municipal.',
                ]);
            }
        }
    }
}
