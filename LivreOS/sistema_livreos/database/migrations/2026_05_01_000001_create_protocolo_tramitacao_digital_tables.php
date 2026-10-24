<?php

/**
 * Componente da aplicação Phoenix - SIGEM (Zero Papel)
 * Migração para Unidades Administrativas, Processos, Documentos e Tramitações
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
        // 1. Unidades Administrativas (Secretarias, Departamentos, Divisões, Gabinete)
        Schema::create('unidades_administrativas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->string('nome', 200);
            $table->string('sigla', 30)->nullable();
            $table->string('tipo', 50)->default('secretaria'); // secretaria, departamento, setor, gabinete, divisao
            $table->unsignedBigInteger('responsavel_id')->nullable()->index();
            $table->string('email', 150)->nullable();
            $table->string('telefone', 50)->nullable();
            $table->boolean('ativo')->default(true);
            $table->integer('ordem')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        // 2. Processos (Protocolo Digital - Zero Papel)
        Schema::create('processos', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 30)->unique(); // Ex: 2026/000001
            $table->integer('ano')->index();
            $table->integer('sequencial')->index();
            $table->unsignedBigInteger('interessado_id')->nullable()->index(); // Cidadão / Contribuinte
            $table->string('assunto', 255);
            $table->text('descricao')->nullable();
            $table->unsignedBigInteger('unidade_atual_id')->nullable()->index();
            $table->unsignedBigInteger('usuario_responsavel_id')->nullable()->index();
            $table->string('status', 30)->default('tramitando')->index(); // tramitando, deferido, indeferido, arquivado, suspenso
            $table->string('prioridade', 20)->default('normal'); // baixa, normal, urgente
            $table->string('sigilo', 20)->default('publico'); // publico, restrito, sigiloso
            $table->integer('prazo_dias')->nullable();
            $table->date('data_limite')->nullable();
            $table->timestamp('data_conclusao')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
        });

        // 3. Documentos e Peças do Processo (Arquivos, Anexos, Pareceres, Despachos)
        Schema::create('processo_documentos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('processo_id')->index();
            $table->string('titulo', 255);
            $table->string('tipo_documento', 50)->default('anexo'); // requerimento, parecer, despacho, oficio, certidao, anexo
            $table->string('caminho_arquivo', 500);
            $table->string('nome_original', 255);
            $table->unsignedBigInteger('tamanho_bytes')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->string('hash_sha256', 64)->nullable(); // Integridade e não-repúdio
            $table->boolean('assinado_digitalmente')->default(false);
            $table->unsignedBigInteger('assinado_por_id')->nullable()->index();
            $table->timestamp('data_assinatura')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        // 4. Tramitações (Motor de Encaminhamento e Histórico de Despacho)
        Schema::create('tramitacoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('processo_id')->index();
            $table->unsignedBigInteger('unidade_origem_id')->index();
            $table->unsignedBigInteger('unidade_destino_id')->index();
            $table->unsignedBigInteger('usuario_envio_id')->index();
            $table->unsignedBigInteger('usuario_recebimento_id')->nullable()->index();
            $table->text('despacho');
            $table->string('parecer_tipo', 30)->default('informativo'); // favoravel, desfavoravel, informativo, diligencia
            $table->string('status', 30)->default('enviado')->index(); // enviado, recebido, concluido
            $table->timestamp('data_envio')->useCurrent();
            $table->timestamp('data_recebimento')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tramitacoes');
        Schema::dropIfExists('processo_documentos');
        Schema::dropIfExists('processos');
        Schema::dropIfExists('unidades_administrativas');
    }
};
