<?php

/**
 * Componente da aplicação Phoenix - SIGEM
 * Modelo: Motorista e Condutor Oficial da Frota Municipal
 *
 * @license AGPL-3.0
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Motorista extends Model
{
    use HasFactory;

    protected $table = 'motoristas';

    protected $fillable = [
        'user_id',
        'cnh_numero',
        'cnh_categoria',
        'cnh_validade',
        'cursos_obrigatorios',
        'status',
        'observacoes',
    ];

    protected $casts = [
        'cnh_validade' => 'date',
        'cursos_obrigatorios' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function viagens(): HasMany
    {
        return $this->hasMany(Viagem::class, 'motorista_id');
    }

    public function abastecimentos(): HasMany
    {
        return $this->hasMany(Abastecimento::class, 'motorista_id');
    }

    public function getNomeAttribute(): string
    {
        return $this->user?->name ?: 'Condutor #' . $this->id;
    }

    public function isCnhVencida(): bool
    {
        return $this->cnh_validade && $this->cnh_validade->isPast();
    }

    public function isCnhVencendoEmBreve(int $dias = 30): bool
    {
        if (! $this->cnh_validade || $this->isCnhVencida()) {
            return false;
        }

        return $this->cnh_validade->diffInDays(Carbon::now()) <= $dias;
    }

    public function getStatusFormatadoAttribute(): string
    {
        if ($this->isCnhVencida()) {
            return 'CNH Vencida';
        }

        return match ($this->status) {
            'apto' => 'Apto para Conduzir',
            'ferias' => 'Em Férias',
            'afastado' => 'Licença / Afastado',
            'suspenso' => 'Suspenso',
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        if ($this->isCnhVencida()) {
            return 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300';
        }

        return match ($this->status) {
            'apto' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
            'ferias' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
            'afastado', 'suspenso' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
