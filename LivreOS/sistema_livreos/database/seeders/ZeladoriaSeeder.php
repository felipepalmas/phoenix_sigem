<?php

namespace Database\Seeders;

use App\Models\CategoriaZeladoria;
use App\Models\StatusOs;
use App\Models\UnidadeAdministrativa;
use Illuminate\Database\Seeder;

class ZeladoriaSeeder extends Seeder
{
    public function run(): void
    {
        $semosp = UnidadeAdministrativa::where('sigla', 'SEMOSP')->first();
        $semma  = UnidadeAdministrativa::where('sigla', 'SEMMA')->first();

        $semospId = $semosp ? $semosp->id : null;
        $semmaId  = $semma ? $semma->id : null;

        $categorias = [
            [
                'sigla' => 'ILUM',
                'nome' => 'Iluminação Pública',
                'descricao' => 'Lâmpada apagada, luminária queimada, braço quebrado, troca de lâmpadas para LED em vias públicas.',
                'secretaria_padrao_id' => $semospId,
                'prazo_padrao_dias' => 3,
                'icone' => 'light-bulb',
                'cor' => 'amber',
                'ordem' => 1,
            ],
            [
                'sigla' => 'ASF',
                'nome' => 'Tapa-Buraco e Pavimentação Asfáltica',
                'descricao' => 'Buraco no pavimento asfáltico, afundamento de pista, manutenção de paralelepípedos.',
                'secretaria_padrao_id' => $semospId,
                'prazo_padrao_dias' => 5,
                'icone' => 'road',
                'cor' => 'blue',
                'ordem' => 2,
            ],
            [
                'sigla' => 'PODA',
                'nome' => 'Poda e Supressão de Árvores',
                'descricao' => 'Poda preventiva de galhos em contato com fiação ou risco de queda em logradouro público.',
                'secretaria_padrao_id' => $semmaId,
                'prazo_padrao_dias' => 7,
                'icone' => 'tree',
                'cor' => 'emerald',
                'ordem' => 3,
            ],
            [
                'sigla' => 'LIMP',
                'nome' => 'Limpeza Urbana, Capina e Entulho',
                'descricao' => 'Capina de calçadas, roçagem de praças e canteiros centrais, recolhimento de entulhos em áreas públicas.',
                'secretaria_padrao_id' => $semospId,
                'prazo_padrao_dias' => 4,
                'icone' => 'trash',
                'cor' => 'green',
                'ordem' => 4,
            ],
            [
                'sigla' => 'DREN',
                'nome' => 'Desobstrução de Bueiros e Galerias',
                'descricao' => 'Limpeza de bocas de lobo, desentupimento de galerias de águas pluviais, reposição de grelhas e tampas.',
                'secretaria_padrao_id' => $semospId,
                'prazo_padrao_dias' => 3,
                'icone' => 'water',
                'cor' => 'cyan',
                'ordem' => 5,
            ],
            [
                'sigla' => 'PRED',
                'nome' => 'Manutenção de Prédios Públicos e Escolas',
                'descricao' => 'Reparos hidráulicos, elétricos, pintura e telhado em escolas, creches, postos de saúde (UBS) e prédios municipais.',
                'secretaria_padrao_id' => $semospId,
                'prazo_padrao_dias' => 10,
                'icone' => 'building',
                'cor' => 'purple',
                'ordem' => 6,
            ],
            [
                'sigla' => 'TRANS',
                'nome' => 'Sinalização Viária e Trânsito',
                'descricao' => 'Pintura de faixas de pedestre, sinalização horizontal, reposição de placas de trânsito e lombadas.',
                'secretaria_padrao_id' => $semospId,
                'prazo_padrao_dias' => 5,
                'icone' => 'traffic',
                'cor' => 'rose',
                'ordem' => 7,
            ],
        ];

        foreach ($categorias as $c) {
            CategoriaZeladoria::firstOrCreate(['sigla' => $c['sigla']], $c);
        }

        // Status Municipais de Zeladoria
        $statusMunicipais = [
            ['nome' => 'Registrada / Triagem', 'cor' => '#3b82f6', 'ordem' => 1, 'marca_inicio' => false, 'marca_conclusao' => false],
            ['nome' => 'Vistoria Técnica Programada', 'cor' => '#f59e0b', 'ordem' => 2, 'marca_inicio' => false, 'marca_conclusao' => false],
            ['nome' => 'Programada para Execução', 'cor' => '#6366f1', 'ordem' => 3, 'marca_inicio' => false, 'marca_conclusao' => false],
            ['nome' => 'Em Execução (Equipe em Campo)', 'cor' => '#8b5cf6', 'ordem' => 4, 'marca_inicio' => true, 'marca_conclusao' => false],
            ['nome' => 'Aguardando Material (Almoxarifado)', 'cor' => '#ec4899', 'ordem' => 5, 'marca_inicio' => false, 'marca_conclusao' => false],
            ['nome' => 'Concluída / Executada', 'cor' => '#10b981', 'ordem' => 6, 'marca_inicio' => false, 'marca_conclusao' => true],
            ['nome' => 'Atestada pelo Fiscal / Cidadão', 'cor' => '#059669', 'ordem' => 7, 'marca_inicio' => false, 'marca_conclusao' => true],
            ['nome' => 'Cancelada / Improcedente', 'cor' => '#ef4444', 'ordem' => 8, 'marca_inicio' => false, 'marca_conclusao' => false],
        ];

        foreach ($statusMunicipais as $st) {
            StatusOs::firstOrCreate(['nome' => $st['nome']], $st);
        }
    }
}
