<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ampliar a tabela depositos com dados municipais
        Schema::table('depositos', function (Blueprint $table) {
            if (!Schema::hasColumn('depositos', 'unidade_administrativa_id')) {
                $table->foreignId('unidade_administrativa_id')->nullable()->after('id')->constrained('unidades_administrativas')->nullOnDelete();
            }
            if (!Schema::hasColumn('depositos', 'codigo')) {
                $table->string('codigo', 50)->nullable()->after('nome');
            }
            if (!Schema::hasColumn('depositos', 'responsavel_user_id')) {
                $table->foreignId('responsavel_user_id')->nullable()->after('localizacao')->constrained('users')->nullOnDelete();
            }
        });

        // 2. Tabela de Requisições de Materiais
        Schema::create('requisicoes_materiais', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 30)->unique(); // REQ-2026/00001
            $table->unsignedSmallInteger('ano')->default(date('Y'));
            $table->foreignId('unidade_solicitante_id')->constrained('unidades_administrativas');
            $table->foreignId('almoxarifado_id')->constrained('depositos');
            $table->foreignId('solicitante_user_id')->constrained('users');
            
            $table->string('situacao', 30)->default('pendente_aprovacao'); 
            // rascunho, pendente_aprovacao, aprovada, atendida_parcial, atendida, recusada, cancelada
            
            $table->string('prioridade', 20)->default('normal'); // normal, urgente
            $table->text('justificativa');
            
            // Aprovação pela chefia da unidade
            $table->foreignId('aprovado_por_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('aprovado_em')->nullable();
            $table->text('parecer_aprovacao')->nullable();
            
            // Atendimento e separação pelo almoxarife
            $table->foreignId('atendido_por_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('atendido_em')->nullable();
            $table->text('observacoes_atendimento')->nullable();
            
            // Confirmação de recebimento pelo solicitante
            $table->foreignId('recebido_por_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('recebido_em')->nullable();
            
            // Vínculo opcional com Protocolo Digital (Zero Papel)
            $table->foreignId('processo_id')->nullable()->constrained('processos')->nullOnDelete();
            
            $table->timestamps();
            
            $table->index(['ano', 'situacao']);
            $table->index('unidade_solicitante_id');
            $table->index('almoxarifado_id');
        });

        // 3. Tabela de Itens da Requisição de Materiais
        Schema::create('requisicao_material_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requisicao_id')->constrained('requisicoes_materiais')->cascadeOnDelete();
            $table->foreignId('produto_id')->constrained('produtos');
            $table->decimal('quantidade_solicitada', 12, 4);
            $table->decimal('quantidade_aprovada', 12, 4)->nullable();
            $table->decimal('quantidade_atendida', 12, 4)->default(0);
            $table->string('observacoes', 255)->nullable();
            $table->timestamps();
            
            $table->index(['requisicao_id', 'produto_id']);
        });

        // 4. Histórico e Kardex de Movimentações de Estoque Municipal
        Schema::create('movimentacoes_estoque', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produto_id')->constrained('produtos');
            $table->foreignId('deposito_id')->constrained('depositos');
            $table->string('tipo', 20); // entrada, saida, transferencia, ajuste
            $table->decimal('quantidade', 12, 4);
            $table->decimal('saldo_anterior', 12, 4)->default(0);
            $table->decimal('saldo_posterior', 12, 4)->default(0);
            $table->string('origem_tipo', 30)->default('avulso'); // requisicao, ordem_servico, nota_empenho, inventario, avulso
            $table->unsignedBigInteger('origem_id')->nullable();
            $table->string('documento_ref', 100)->nullable(); // ex.: "REQ-2026/00001", "NF/Empenho 123/2026"
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('justificativa')->nullable();
            $table->timestamps();
            
            $table->index(['produto_id', 'deposito_id']);
            $table->index(['origem_tipo', 'origem_id']);
            $table->index('tipo');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimentacoes_estoque');
        Schema::dropIfExists('requisicao_material_itens');
        Schema::dropIfExists('requisicoes_materiais');
        
        Schema::table('depositos', function (Blueprint $table) {
            if (Schema::hasColumn('depositos', 'unidade_administrativa_id')) {
                $table->dropForeign(['unidade_administrativa_id']);
                $table->dropColumn('unidade_administrativa_id');
            }
            if (Schema::hasColumn('depositos', 'codigo')) {
                $table->dropColumn('codigo');
            }
            if (Schema::hasColumn('depositos', 'responsavel_user_id')) {
                $table->dropForeign(['responsavel_user_id']);
                $table->dropColumn('responsavel_user_id');
            }
        });
    }
};
