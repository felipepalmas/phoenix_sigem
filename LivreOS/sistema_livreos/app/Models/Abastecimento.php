<?php

/**
 * Componente da aplicação Phoenix - SIGEM
 * Modelo: Abastecimento de Combustível da Frota Municipal
 *
 * @license AGPL-3.0
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Abastecimento extends Model
{
    use HasFactory;

    protected $table = 'abastecimentos';

    protected $fillable = [
        'veiculo_id',
        'motorista_id',
        'data',
        'combustivel',
        'litros',
        'valor_litro',
        'valor_total',
        'km_veiculo',
        'km_anterior',
        'consumo_medio_kml',
        'posto_combustivel',
        'numero_cupom_fiscal',
        'observacoes',
    ];

    protected $casts = [
        'data' => 'datetime',
        'litros' => 'decimal:2',
        'valor_litro' => 'decimal:2',
        'valor_total' => 'decimal:2',
        'km_veiculo' => 'integer',
        'km_anterior' => 'integer',
        'consumo_medio_kml' => 'decimal:2',
    ];

    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }

    public function motorista(): BelongsTo
    {
        return $this->belongsTo(Motorista::class, 'motorista_id');
    }
}
