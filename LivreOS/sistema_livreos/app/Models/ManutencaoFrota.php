<?php

/**
 * Componente da aplicação Phoenix - SIGEM
 * Modelo: Manutenção Preventiva e Corretiva da Frota Municipal
 *
 * @license AGPL-3.0
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManutencaoFrota extends Model
{
    use HasFactory;

    protected $table = 'manutencoes_frota';

    protected $fillable = [
        'numero',
        'veiculo_id',
        'tipo',
        'km_veiculo',
        'data_agendamento',
        'data_conclusao',
        'oficina_executora',
        'descricao_problema',
        'servicos_executados',
        'valor_total_estimado',
        'requisicao_material_id',
        'status',
        'responsavel_user_id',
    ];

    protected $casts = [
        'data_agendamento' => 'date',
        'data_conclusao' => 'datetime',
        'km_veiculo' => 'integer',
        'valor_total_estimado' => 'decimal:2',
    ];

    public static function gerarProximoNumero(int $ano = null): string
    {
        $ano = $ano ?? (int) date('Y');
        $ultimoId = self::whereYear('data_agendamento', $ano)->max('id') ?? 0;
        return sprintf('MAN-%d/%04d', $ano, $ultimoId + 1);
    }

    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }

    public function requisicaoMaterial(): BelongsTo
    {
        return $this->belongsTo(RequisicaoMaterial::class, 'requisicao_material_id');
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsavel_user_id');
    }

    public function getStatusFormatadoAttribute(): string
    {
        return match ($this->status) {
            'agendada' => 'Agendada',
            'em_execucao' => 'Na Oficina / Em Execução',
            'concluida' => 'Concluída / Liberado',
            'cancelada' => 'Cancelada',
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'agendada' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
            'em_execucao' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
            'concluida' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
            'cancelada' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
