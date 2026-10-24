<?php

/**
 * Componente da aplicação Phoenix - SIGEM
 *
 * Revisão técnica nº 2 (24/10/2026):
 *  - numeradores (numeração oficial sem corrida: ZEL, REQ, VIA);
 *  - código de zeladoria único;
 *  - históricos (viagens, abastecimentos, manutenções, auditoria) deixam de ser apagados em cascata;
 *  - chaves estrangeiras do Protocolo;
 *  - permissões municipais.
 *
 * Idempotente: pode ser executada de novo depois de uma falha parcial.
 * No SQLite as chaves estrangeiras são trocadas recriando a tabela manualmente
 * (o rebuild automático do Laravel falha em tabelas com UNIQUE embutido, "sqlite_autoindex_*").
 *
 * @license AGPL-3.0
 */

use App\Support\PermissoesMunicipais;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** O SQLite não troca PRAGMA foreign_keys dentro de transação. */
    public $withinTransaction = false;

    /** tabela => [coluna => tabela referenciada] que devem ficar ON DELETE RESTRICT */
    private array $restrict = [
        'viagens' => ['veiculo_id' => 'veiculos', 'motorista_id' => 'motoristas'],
        'abastecimentos' => ['veiculo_id' => 'veiculos'],
        'manutencoes_frota' => ['veiculo_id' => 'veiculos'],
        'audit_processos_sigilosos' => ['processo_id' => 'processos'],
        'tramitacoes' => ['processo_id' => 'processos', 'unidade_origem_id' => 'unidades_administrativas', 'unidade_destino_id' => 'unidades_administrativas'],
        'processo_documentos' => ['processo_id' => 'processos'],
        'processos' => ['unidade_atual_id' => 'unidades_administrativas'],
    ];

    public function up(): void
    {
        if (! Schema::hasTable('numeradores')) {
            Schema::create('numeradores', function (Blueprint $table) {
                $table->id();
                $table->string('serie', 20);
                $table->integer('ano');
                $table->unsignedBigInteger('ultimo')->default(0);
                $table->timestamps();
                $table->unique(['serie', 'ano']);
            });
        }

        if (! Schema::hasIndex('ordem_servicos', 'ordem_servicos_codigo_interno_unique')) {
            Schema::table('ordem_servicos', function (Blueprint $table) {
                $table->unique('codigo_interno', 'ordem_servicos_codigo_interno_unique');
            });
        }

        foreach ($this->restrict as $tabela => $colunas) {
            if (Schema::hasTable($tabela)) {
                $this->garantirRestrict($tabela, $colunas);
            }
        }

        PermissoesMunicipais::garantir();
    }

    public function down(): void
    {
        PermissoesMunicipais::remover();
        if (Schema::hasIndex('ordem_servicos', 'ordem_servicos_codigo_interno_unique')) {
            Schema::table('ordem_servicos', fn (Blueprint $t) => $t->dropUnique('ordem_servicos_codigo_interno_unique'));
        }
        Schema::dropIfExists('numeradores');
        // As chaves estrangeiras permanecem RESTRICT no rollback (mais seguro para o histórico).
    }

    /** Situação atual: coluna => on_delete ('cascade', 'restrict', ...) ou ausente. */
    private function fksAtuais(string $tabela): array
    {
        $mapa = [];
        foreach (Schema::getForeignKeys($tabela) as $fk) {
            if (count($fk['columns']) === 1) {
                $mapa[$fk['columns'][0]] = strtolower((string) ($fk['on_delete'] ?? ''));
            }
        }

        return $mapa;
    }

    private function garantirRestrict(string $tabela, array $colunas): void
    {
        $atuais = $this->fksAtuais($tabela);
        $pendentes = array_filter($colunas, fn ($ref, $col) => ($atuais[$col] ?? null) !== 'restrict', ARRAY_FILTER_USE_BOTH);
        if (! $pendentes) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            $this->sqliteRecriarComRestrict($tabela, $pendentes, $atuais);

            return;
        }

        Schema::table($tabela, function (Blueprint $table) use ($pendentes, $atuais) {
            foreach ($pendentes as $col => $ref) {
                if (isset($atuais[$col])) {
                    $table->dropForeign([$col]);
                }
                $table->foreign($col)->references('id')->on($ref)->restrictOnDelete();
            }
        });
    }

    private function sqliteRecriarComRestrict(string $tabela, array $pendentes, array $atuais): void
    {
        $sql = DB::selectOne("select sql from sqlite_master where type = 'table' and name = ?", [$tabela])->sql;
        $novo = $sql;
        foreach ($pendentes as $col => $ref) {
            $padrao = '/(foreign\s+key\s*\(\s*"?' . preg_quote($col, '/') . '"?\s*\)\s*references\s*"?\w+"?\s*\(\s*"?\w+"?\s*\)\s*on\s+delete\s+)(cascade|set\s+null|no\s+action|set\s+default)/i';
            if (isset($atuais[$col]) && preg_match($padrao, $novo)) {
                $novo = preg_replace($padrao, '${1}restrict', $novo, 1);
            } elseif (! isset($atuais[$col])) {
                $fim = strrpos($novo, ')');
                $novo = substr($novo, 0, $fim) . ', foreign key("' . $col . '") references "' . $ref . '"("id") on delete restrict' . substr($novo, $fim);
            }
        }
        if ($novo === $sql) {
            return;
        }

        $indices = DB::select("select sql from sqlite_master where type = 'index' and tbl_name = ? and sql is not null", [$tabela]);
        $temporaria = $tabela . '__sigem_tmp';
        $criar = preg_replace('/^\s*CREATE\s+TABLE\s+"?' . preg_quote($tabela, '/') . '"?/i', 'CREATE TABLE "' . $temporaria . '"', $novo, 1);

        DB::statement('PRAGMA foreign_keys = OFF');
        DB::beginTransaction();
        try {
            DB::statement('DROP TABLE IF EXISTS "' . $temporaria . '"');
            DB::statement($criar);
            DB::statement('INSERT INTO "' . $temporaria . '" SELECT * FROM "' . $tabela . '"');
            DB::statement('DROP TABLE "' . $tabela . '"');
            DB::statement('ALTER TABLE "' . $temporaria . '" RENAME TO "' . $tabela . '"');
            foreach ($indices as $indice) {
                DB::statement($indice->sql);
            }
            $violacoes = DB::select('PRAGMA foreign_key_check("' . $tabela . '")');
            if (count($violacoes) > 0) {
                throw new \RuntimeException("A tabela {$tabela} tem " . count($violacoes) . ' registro(s) apontando para dados inexistentes. Corrija antes de migrar.');
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            DB::statement('PRAGMA foreign_keys = ON');
            throw $e;
        }
        DB::statement('PRAGMA foreign_keys = ON');
    }
};
