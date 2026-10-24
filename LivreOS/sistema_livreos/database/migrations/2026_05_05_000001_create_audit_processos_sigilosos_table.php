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
        Schema::create('audit_processos_sigilosos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('processo_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('unidade_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('acao', 50)->default('visualizacao'); // visualizacao, download_documento, despacho, alteracao_sigilo
            $table->text('justificativa')->nullable();
            $table->timestamps();

            $table->foreign('processo_id')->references('id')->on('processos')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_processos_sigilosos');
    }
};
