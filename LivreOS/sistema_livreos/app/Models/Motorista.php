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

    /** Cursos especializados (valor gravado => rótulo). */
    public const CURSOS = [
        'direcao_defensiva' => 'Direção defensiva',
        'primeiros_socorros' => 'Primeiros socorros',
        'condutor_emergencia_samu' => 'Condutor de veículo de emergência',
        'transporte_escolar' => 'Transporte escolar',
        'transporte_coletivo' => 'Transporte coletivo de passageiros',
        'cargas_perigosas_mopp' => 'Cargas perigosas (MOPP)',
        'cargas_indivisiveis' => 'Cargas indivisíveis',
        'operador_maquinas_pesadas' => 'Operador de máquinas pesadas',
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

    /** Rótulos dos cursos (aceita também registros antigos gravados com o texto). */
    public function getCursosFormatadosAttribute(): array
    {
        return array_values(array_map(
            fn ($curso) => self::CURSOS[$curso] ?? (string) $curso,
            array_filter((array) ($this->cursos_obrigatorios ?? []))
        ));
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

        // Carbon 3: diffInDays é com sinal; mede de hoje até a validade (positivo no futuro).
        return Carbon::today()->diffInDays($this->cnh_validade->copy()->startOfDay(), false) <= $dias;
    }

    /** Dias até a validade da CNH (negativo se já venceu). */
    public function getDiasParaVencerCnhAttribute(): ?int
    {
        return $this->cnh_validade ? (int) Carbon::today()->diffInDays($this->cnh_validade->copy()->startOfDay(), false) : null;
    }

    public function getStatusFormatadoAttribute(): string
    {
        if ($this->isCnhVencida()) {
            return 'CNH vencida';
        }

        return match ($this->status) {
            'apto' => 'Apto',
            'ferias' => 'Em férias',
            'afastado' => 'Afastado',
            'suspenso' => 'Suspenso',
            default => ucfirst((string) $this->status),
        };
    }

    /** Tom do badge SIGEM (info|ok|warn|danger|neutral). */
    public function getStatusTomAttribute(): string
    {
        if ($this->isCnhVencida()) {
            return 'danger';
        }

        return match ($this->status) {
            'apto' => 'ok',
            'afastado' => 'warn',
            'suspenso' => 'danger',
            default => 'neutral',
        };
    }

    /** Pode ser escalado para uma saída agora (apto e CNH válida). */
    public function podeConduzir(): bool
    {
        return $this->status === 'apto' && ! $this->isCnhVencida();
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
