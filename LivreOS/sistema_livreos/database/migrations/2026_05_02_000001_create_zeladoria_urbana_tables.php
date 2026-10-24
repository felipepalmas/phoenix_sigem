<?php

/**
 * Componente da aplicação Phoenix - SIGEM
 * Módulo B: Zeladoria Urbana e Serviços Públicos Municipais
 *
 * @license AGPL-3.0
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Categorias de Serviços Públicos de Zeladoria
        Schema::create('categorias_zeladoria', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 150);
            $table->string('sigla', 20)->nullable();
            $table->text('descricao')->nullable();
            $table->unsignedBigInteger('secretaria_padrao_id')->nullable()->index();
            $table->integer('prazo_padrao_dias')->default(7);
            $table->string('icone', 50)->default('wrench');
            $table->string('cor', 30)->default('blue');
            $table->boolean('ativo')->default(true);
            $table->integer('ordem')->default(0);
            $table->timestamps();
        });

        // 2. Adicionar campos municipais de Zeladoria Urbana na tabela ordem_servicos
        Schema::table('ordem_servicos', function (Blueprint $table) {
            $table->unsignedBigInteger('categoria_zeladoria_id')->nullable()->index();
            $table->unsignedBigInteger('processo_id')->nullable()->index(); // Vínculo com Protocolo Digital (Módulo A)
            $table->unsignedBigInteger('secretaria_responsavel_id')->nullable()->index(); // Vínculo com Unidade Administrativa
            
            // Localização e Logradouro da Demanda
            $table->string('logradouro', 255)->nullable();
            $table->string('numero_logradouro', 30)->nullable();
            $table->string('bairro', 100)->nullable();
            $table->string('cep', 10)->nullable();
            $table->string('referencia_local', 255)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Registro Fotográfico e Comprovação (Antes, Durante e Depois)
            $table->string('foto_antes', 500)->nullable();
            $table->string('foto_durante', 500)->nullable();
            $table->string('foto_depois', 500)->nullable();

            // Origem e Atesto do Serviço Público
            $table->string('origem_demanda', 50)->default('balcao'); // balcao, telefone_156, ouvidoria, fiscalizacao, processo_protocolo, portal
            $table->string('atesto_responsavel', 150)->nullable();
            $table->string('atesto_cargo', 100)->nullable();
            $table->timestamp('atesto_em')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('ordem_servicos', function (Blueprint $table) {
            $table->dropColumn([
                'categoria_zeladoria_id',
                'processo_id',
                'secretaria_responsavel_id',
                'logradouro',
                'numero_logradouro',
                'bairro',
                'cep',
                'referencia_local',
                'latitude',
                'longitude',
                'foto_antes',
                'foto_durante',
                'foto_depois',
                'origem_demanda',
                'atesto_responsavel',
                'atesto_cargo',
                'atesto_em',
            ]);
        });

        Schema::dropIfExists('categorias_zeladoria');
    }
};
