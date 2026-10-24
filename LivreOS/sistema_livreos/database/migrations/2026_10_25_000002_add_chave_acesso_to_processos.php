<?php

/**
 * Componente da aplicação Phoenix - SIGEM
 *
 * Chave de acesso do comprovante: a consulta pública de processo passa a exigir
 * número + chave, impedindo a varredura sequencial (2026/000001, 000002...).
 *
 * @license AGPL-3.0
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('processos', function (Blueprint $table) {
            $table->string('chave_acesso', 12)->nullable()->after('numero');
        });

        DB::table('processos')->whereNull('chave_acesso')->orderBy('id')->each(function ($p) {
            DB::table('processos')->where('id', $p->id)->update(['chave_acesso' => \App\Models\Processo::novaChaveAcesso()]);
        });
    }

    public function down(): void
    {
        Schema::table('processos', function (Blueprint $table) {
            $table->dropColumn('chave_acesso');
        });
    }
};
