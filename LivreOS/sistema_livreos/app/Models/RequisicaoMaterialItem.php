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

class RequisicaoMaterialItem extends Model
{
    use HasFactory;

    protected $table = 'requisicao_material_itens';

    protected $fillable = [
        'requisicao_id',
        'produto_id',
        'quantidade_solicitada',
        'quantidade_aprovada',
        'quantidade_atendida',
        'observacoes',
    ];

    protected $casts = [
        'quantidade_solicitada' => 'decimal:4',
        'quantidade_aprovada'   => 'decimal:4',
        'quantidade_atendida'   => 'decimal:4',
    ];

    public function requisicao()
    {
        return $this->belongsTo(RequisicaoMaterial::class, 'requisicao_id');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }
}
