<?php

/**
 * Componente da aplicação Phoenix - SIGEM
 *
 * Chave de acesso das demandas de zeladoria: sem ela o portal mostra só o andamento
 * (serviço, bairro e situação), sem relato, endereço, fotos ou dados do solicitante.
 *
 * @license AGPL-3.0
 */

use App\Models\Processo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordem_servicos', function (Blueprint $table) {
            $table->string('chave_acesso', 12)->nullable();
        });

        DB::table('ordem_servicos')->whereNotNull('categoria_zeladoria_id')->whereNull('chave_acesso')->orderBy('id')
            ->each(fn ($os) => DB::table('ordem_servicos')->where('id', $os->id)->update(['chave_acesso' => Processo::novaChaveAcesso()]));
    }

    public function down(): void
    {
        Schema::table('ordem_servicos', function (Blueprint $table) {
            $table->dropColumn('chave_acesso');
        });
    }
};
