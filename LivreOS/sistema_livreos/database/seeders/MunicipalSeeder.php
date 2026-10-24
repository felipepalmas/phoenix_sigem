<?php

namespace Database\Seeders;

use App\Models\CentroCusto;
use App\Models\UnidadeAdministrativa;
use Illuminate\Database\Seeder;

class MunicipalSeeder extends Seeder
{
    public function run(): void
    {
        $unidades = [
            ['sigla' => 'GAB', 'nome' => 'Gabinete do Prefeito', 'tipo' => 'gabinete', 'ordem' => 1],
            ['sigla' => 'SEMAD', 'nome' => 'Secretaria Municipal de Administração e Recursos Humanos', 'tipo' => 'secretaria', 'ordem' => 2],
            ['sigla' => 'SEFAZ', 'nome' => 'Secretaria Municipal de Fazenda, Finanças e Tributação', 'tipo' => 'secretaria', 'ordem' => 3],
            ['sigla' => 'SEMOSP', 'nome' => 'Secretaria Municipal de Obras e Serviços Públicos', 'tipo' => 'secretaria', 'ordem' => 4],
            ['sigla' => 'SEMED', 'nome' => 'Secretaria Municipal de Educação e Cultura', 'tipo' => 'secretaria', 'ordem' => 5],
            ['sigla' => 'SEMS', 'nome' => 'Secretaria Municipal de Saúde', 'tipo' => 'secretaria', 'ordem' => 6],
            ['sigla' => 'SEMMA', 'nome' => 'Secretaria Municipal de Meio Ambiente', 'tipo' => 'secretaria', 'ordem' => 7],
            ['sigla' => 'PROGEM', 'nome' => 'Procuradoria Geral do Município', 'tipo' => 'setor', 'ordem' => 8],
        ];

        foreach ($unidades as $u) {
            UnidadeAdministrativa::firstOrCreate(['sigla' => $u['sigla']], $u);
            CentroCusto::firstOrCreate(['codigo' => $u['sigla']], [
                'nome' => $u['nome'],
                'ordem' => $u['ordem'],
                'ativo' => true,
            ]);
        }
    }
}
