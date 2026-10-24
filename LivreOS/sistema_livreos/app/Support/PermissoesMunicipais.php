<?php

/**
 * Componente da aplicação Phoenix - SIGEM
 *
 * @license AGPL-3.0
 */

namespace App\Support;

use App\Models\Permission;
use App\Models\Role;

/**
 * Permissões próprias do SIGEM e os papéis que as recebem por padrão.
 * Chamado pela migration (bancos existentes) e pelo seeder (instalação nova,
 * em que os papéis só existem depois do RolePermissionSeeder).
 */
class PermissoesMunicipais
{
    /** slug => [nome, descrição, papéis padrão] */
    public const LISTA = [
        'protocolo.sigiloso' => ['Ver processos sigilosos e restritos', 'Acessa processos restritos/sigilosos de qualquer secretaria. Fica registrado na auditoria.', []],
        'almoxarifado.aprovar' => ['Aprovar requisições de material', 'Aprova ou recusa requisições da secretaria em que o servidor está lotado.', ['manager']],
        'almoxarifado.atender' => ['Atender requisições (almoxarife)', 'Separa materiais, dá baixa no estoque e registra entradas no almoxarifado da sua secretaria.', ['manager']],
        'zeladoria.concluir' => ['Concluir demandas de zeladoria', 'Atualiza e atesta demandas da secretaria em que o servidor está lotado.', ['manager', 'technician']],
        'frota.gerenciar' => ['Gerenciar frota', 'Cadastra veículos e motoristas oficiais.', ['manager']],
    ];

    public static function garantir(): void
    {
        foreach (self::LISTA as $slug => [$nome, $descricao, $papeis]) {
            $permissao = Permission::firstOrCreate(['slug' => $slug], ['name' => $nome, 'description' => $descricao]);
            foreach ($papeis as $papel) {
                $role = Role::where('slug', $papel)->first();
                if ($role && ! $role->permissions()->where('permissions.id', $permissao->id)->exists()) {
                    $role->permissions()->attach($permissao->id);
                }
            }
        }
    }

    public static function remover(): void
    {
        Permission::whereIn('slug', array_keys(self::LISTA))->delete();
    }
}
