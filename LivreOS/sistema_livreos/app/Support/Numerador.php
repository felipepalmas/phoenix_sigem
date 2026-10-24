<?php

/**
 * Componente da aplicação Phoenix - SIGEM
 *
 * @license AGPL-3.0
 */

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Numeração oficial sequencial por série e ano (ZEL, REQ, VIA...), sem corrida.
 *
 * Usa a tabela `numeradores` com SELECT ... FOR UPDATE. Deve ser chamado DENTRO da
 * mesma transação que grava o registro numerado: o lock só é liberado no commit,
 * então duas gravações simultâneas nunca recebem o mesmo número.
 */
class Numerador
{
    /**
     * @param  string  $serie  Ex.: 'ZEL', 'REQ'
     * @param  int  $ano  Ano de referência
     * @param  callable|null  $valorInicial  fn(int $ano): int — maior número já usado
     *                                       antes da série existir (migração de dados legados)
     */
    public static function proximo(string $serie, int $ano, ?callable $valorInicial = null): int
    {
        return DB::transaction(function () use ($serie, $ano, $valorInicial) {
            // Garante a linha ANTES do lock: no MySQL, SELECT ... FOR UPDATE sobre linha inexistente
            // pega gap lock e dois inserts concorrentes entrariam em deadlock.
            if (! DB::table('numeradores')->where('serie', $serie)->where('ano', $ano)->exists()) {
                DB::table('numeradores')->insertOrIgnore([
                    'serie' => $serie,
                    'ano' => $ano,
                    'ultimo' => $valorInicial ? (int) $valorInicial($ano) : 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $linha = DB::table('numeradores')
                ->where('serie', $serie)
                ->where('ano', $ano)
                ->lockForUpdate()
                ->first();

            $proximo = ((int) $linha->ultimo) + 1;

            DB::table('numeradores')
                ->where('id', $linha->id)
                ->update(['ultimo' => $proximo, 'updated_at' => now()]);

            return $proximo;
        }, 3);
    }

    /**
     * Maior sufixo numérico já usado num campo com formato "PREFIXO-AAAA/NNNNN".
     */
    public static function maiorSufixo(string $tabela, string $coluna, string $prefixo): int
    {
        $maior = 0;
        DB::table($tabela)
            ->where($coluna, 'like', $prefixo . '%')
            ->pluck($coluna)
            ->each(function ($valor) use (&$maior) {
                if (preg_match('/(\d+)\s*$/', (string) $valor, $m)) {
                    $maior = max($maior, (int) $m[1]);
                }
            });

        return $maior;
    }
}
