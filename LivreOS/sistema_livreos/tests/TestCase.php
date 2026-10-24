<?php

namespace Tests;

use App\Models\Role;
use App\Models\UnidadeAdministrativa;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SigemPermissoesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed([RolePermissionSeeder::class, SigemPermissoesSeeder::class]);
    }

    protected function unidade(string $sigla): UnidadeAdministrativa
    {
        return UnidadeAdministrativa::firstOrCreate(['sigla' => $sigla], ['nome' => 'Secretaria ' . $sigla, 'tipo' => 'secretaria', 'ativo' => true]);
    }

    /** Servidor com papel e lotação. */
    protected function servidor(?UnidadeAdministrativa $lotacao = null, string $papel = 'user', array $extra = []): User
    {
        $user = User::create(array_merge([
            'name' => 'Servidor ' . uniqid(),
            'email' => uniqid() . '@nonoai.test',
            'password' => bcrypt('senha-de-teste-123'),
            'ativo' => true,
            'unidade_administrativa_id' => $lotacao?->id,
        ], $extra));
        $user->roles()->attach(Role::where('slug', $papel)->value('id'));

        return $user->fresh();
    }
}
