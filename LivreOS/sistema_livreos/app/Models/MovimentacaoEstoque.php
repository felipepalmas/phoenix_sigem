<?php

/**
 * Componente da aplicação Phoenix - SIGEM
 *
 * @author    viniciusvams / Phoenix - SIGEM
 * @copyright 2024-2026 LivreOS / Phoenix
 * @license   https://www.gnu.org/licenses/agpl-3.0.txt AGPL-3.0
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MovimentacaoEstoque extends Model
{
    use HasFactory;

    protected $table = 'movimentacoes_estoque';

    protected $fillable = [
        'produto_id',
        'deposito_id',
        'tipo', // entrada, saida, transferencia, ajuste
        'quantidade',
        'saldo_anterior',
        'saldo_posterior',
        'origem_tipo', // requisicao, ordem_servico, nota_empenho, inventario, avulso
        'origem_id',
        'documento_ref',
        'user_id',
        'justificativa',
    ];

    protected $casts = [
        'quantidade'      => 'decimal:4',
        'saldo_anterior'  => 'decimal:4',
        'saldo_posterior' => 'decimal:4',
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }

    public function deposito()
    {
        return $this->belongsTo(Deposito::class, 'deposito_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Registra movimentação de estoque com atualização atômica de saldo no produto
     *
     * @param int $produtoId
     * @param int $depositoId
     * @param string $tipo entrada|saida|ajuste|transferencia
     * @param float $quantidade
     * @param string $origemTipo
     * @param int|null $origemId
     * @param string|null $documentoRef
     * @param int|null $userId
     * @param string|null $justificativa
     * @return MovimentacaoEstoque
     */
    public static function registrar(
        int $produtoId,
        int $depositoId,
        string $tipo,
        float $quantidade,
        string $origemTipo = 'avulso',
        ?int $origemId = null,
        ?string $documentoRef = null,
        ?int $userId = null,
        ?string $justificativa = null
    ): self {
        return DB::transaction(function () use (
            $produtoId,
            $depositoId,
            $tipo,
            $quantidade,
            $origemTipo,
            $origemId,
            $documentoRef,
            $userId,
            $justificativa
        ) {
            $produto = Produto::lockForUpdate()->findOrFail($produtoId);
            $saldoAnterior = (float) ($produto->estoque_quantidade ?? 0);

            if ($tipo === 'saida') {
                if ($saldoAnterior < $quantidade) {
                    throw new \DomainException("Saldo insuficiente em estoque para o produto '{$produto->nome}'. Saldo disponível: {$saldoAnterior}, Saída solicitada: {$quantidade}.");
                }
                $saldoPosterior = $saldoAnterior - $quantidade;
            } elseif ($tipo === 'entrada') {
                $saldoPosterior = $saldoAnterior + $quantidade;
            } elseif ($tipo === 'ajuste') {
                $saldoPosterior = $quantidade;
            } else {
                $saldoPosterior = $saldoAnterior - $quantidade;
            }

            $produto->estoque_quantidade = $saldoPosterior;
            $produto->save();

            return self::create([
                'produto_id'      => $produtoId,
                'deposito_id'     => $depositoId,
                'tipo'            => $tipo,
                'quantidade'      => $quantidade,
                'saldo_anterior'  => $saldoAnterior,
                'saldo_posterior' => $saldoPosterior,
                'origem_tipo'     => $origemTipo,
                'origem_id'       => $origemId,
                'documento_ref'   => $documentoRef,
                'user_id'         => $userId ?? auth()->id(),
                'justificativa'   => $justificativa,
            ]);
        });
    }
}
