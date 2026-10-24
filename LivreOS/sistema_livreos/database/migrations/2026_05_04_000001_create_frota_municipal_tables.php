<?php

/**
 * Componente da aplicação Phoenix - SIGEM
 * Migration: Módulo D — Frota Municipal, Diário de Bordo, Motoristas e Abastecimentos
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
        // 1. Tabela de Veículos e Máquinas da Frota Municipal
        Schema::create('veiculos', function (Blueprint $table) {
            $table->id();
            $table->string('placa', 10)->unique();
            $table->string('modelo', 100);
            $table->string('marca', 60);
            $table->integer('ano_fabricacao');
            $table->integer('ano_modelo');
            $table->string('tipo', 40); // passeio, utilitario, caminhao, onibus_escolar, ambulancia, maquina_pesada, motocicleta
            $table->string('combustivel', 30); // flex, gasolina, etanol, diesel_s10, eletrico
            $table->string('renavam', 30)->nullable();
            $table->string('chassi', 40)->nullable();
            $table->string('patrimonio_tombamento', 40)->nullable();
            $table->foreignId('unidade_administrativa_id')->nullable()->constrained('unidades_administrativas')->nullOnDelete();
            $table->foreignId('motorista_responsavel_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('km_atual')->default(0);
            $table->string('status', 30)->default('disponivel'); // disponivel, em_viagem, em_manutencao, inoperante, baixado
            $table->decimal('capacidade_tanque', 8, 2)->nullable();
            $table->integer('capacidade_passageiros')->nullable();
            $table->string('foto_path')->nullable();
            $table->text('observacoes')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        // 2. Tabela de Motoristas e Condutores Oficiais
        Schema::create('motoristas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('cnh_numero', 30)->unique();
            $table->string('cnh_categoria', 10); // A, B, C, D, E, AB, AD, AE
            $table->date('cnh_validade');
            $table->json('cursos_obrigatorios')->nullable(); // transporte_escolar, emergencia, cargas_perigosas
            $table->string('status', 30)->default('apto'); // apto, ferias, afastado, suspenso
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });

        // 3. Diário de Bordo Digital (Viagens, Saídas e Retornos de Veículos)
        Schema::create('viagens', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 30)->unique(); // VIA-2026/00001
            $table->foreignId('veiculo_id')->constrained('veiculos')->restrictOnDelete();
            $table->foreignId('motorista_id')->constrained('motoristas')->restrictOnDelete();
            $table->foreignId('solicitante_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('unidade_administrativa_id')->nullable()->constrained('unidades_administrativas')->nullOnDelete();
            $table->dateTime('data_saida');
            $table->dateTime('data_retorno')->nullable();
            $table->integer('km_saida');
            $table->integer('km_retorno')->nullable();
            $table->integer('km_percorrido')->nullable();
            $table->string('destino', 200);
            $table->text('finalidade');
            $table->string('situacao', 30)->default('agendada'); // agendada, em_andamento, concluida, cancelada
            $table->foreignId('processo_id')->nullable()->constrained('processos')->nullOnDelete();
            $table->json('checklist_saida')->nullable();
            $table->json('checklist_retorno')->nullable();
            $table->text('ocorrencias')->nullable();
            $table->timestamps();
        });

        // 4. Abastecimentos Municipais e Controle de Consumo
        Schema::create('abastecimentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('veiculo_id')->constrained('veiculos')->restrictOnDelete();
            $table->foreignId('motorista_id')->nullable()->constrained('motoristas')->nullOnDelete();
            $table->dateTime('data');
            $table->string('combustivel', 30);
            $table->decimal('litros', 10, 2);
            $table->decimal('valor_litro', 10, 2);
            $table->decimal('valor_total', 10, 2);
            $table->integer('km_veiculo');
            $table->integer('km_anterior')->nullable();
            $table->decimal('consumo_medio_kml', 8, 2)->nullable();
            $table->string('posto_combustivel', 100);
            $table->string('numero_cupom_fiscal', 50)->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });

        // 5. Ordens de Manutenção da Frota
        Schema::create('manutencoes_frota', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 30)->unique(); // MAN-2026/0001
            $table->foreignId('veiculo_id')->constrained('veiculos')->restrictOnDelete();
            $table->string('tipo', 30); // preventiva, corretiva
            $table->integer('km_veiculo');
            $table->date('data_agendamento');
            $table->dateTime('data_conclusao')->nullable();
            $table->string('oficina_executora', 100); // oficina_propria, oficina_credenciada, concessionaria
            $table->text('descricao_problema');
            $table->text('servicos_executados')->nullable();
            $table->decimal('valor_total_estimado', 10, 2)->default(0);
            $table->foreignId('requisicao_material_id')->nullable()->constrained('requisicoes_materiais')->nullOnDelete();
            $table->string('status', 30)->default('agendada'); // agendada, em_execucao, concluida, cancelada
            $table->foreignId('responsavel_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manutencoes_frota');
        Schema::dropIfExists('abastecimentos');
        Schema::dropIfExists('viagens');
        Schema::dropIfExists('motoristas');
        Schema::dropIfExists('veiculos');
    }
};
