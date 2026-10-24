<?php

namespace Database\Seeders;

use App\Support\PermissoesMunicipais;
use Illuminate\Database\Seeder;

/**
 * Garante as permissões municipais do SIGEM nos papéis padrão (instalação nova).
 */
class SigemPermissoesSeeder extends Seeder
{
    public function run(): void
    {
        PermissoesMunicipais::garantir();
    }
}
