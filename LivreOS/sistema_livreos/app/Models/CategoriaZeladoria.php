<?php

/**
 * Componente da aplicação Phoenix - SIGEM
 * Modelo de Categorias de Serviços Públicos de Zeladoria Urbana
 *
 * @license AGPL-3.0
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaZeladoria extends Model
{
    use HasFactory;

    protected $table = 'categorias_zeladoria';

    protected $fillable = [
        'nome',
        'sigla',
        'descricao',
        'secretaria_padrao_id',
        'prazo_padrao_dias',
        'icone',
        'cor',
        'ativo',
        'ordem',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'ordem' => 'integer',
        'prazo_padrao_dias' => 'integer',
    ];

    public function secretariaPadrao(): BelongsTo
    {
        return $this->belongsTo(UnidadeAdministrativa::class, 'secretaria_padrao_id');
    }

    public function ordensServico(): HasMany
    {
        return $this->hasMany(OrdemServico::class, 'categoria_zeladoria_id');
    }

    public function getBadgeColorAttribute(): string
    {
        return match ($this->cor) {
            'amber', 'yellow' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
            'emerald', 'green' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
            'purple' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300',
            'rose', 'red' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300',
            'indigo' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300',
            default => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
        };
    }
}
