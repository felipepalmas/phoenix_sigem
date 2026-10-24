<?php

/**
 * Componente da aplicação Phoenix - SIGEM
 * Modelo: Viagem (Diário de Bordo Digital da Frota Municipal)
 *
 * @license AGPL-3.0
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Viagem extends Model
{
    use HasFactory;

    protected $table = 'viagens';

    protected $fillable = [
        'numero',
        'veiculo_id',
        'motorista_id',
        'solicitante_user_id',
        'unidade_administrativa_id',
        'data_saida',
        'data_retorno',
        'km_saida',
        'km_retorno',
        'km_percorrido',
        'destino',
        'finalidade',
        'situacao',
        'processo_id',
        'checklist_saida',
        'checklist_retorno',
        'ocorrencias',
    ];

    protected $casts = [
        'data_saida' => 'datetime',
        'data_retorno' => 'datetime',
        'km_saida' => 'integer',
        'km_retorno' => 'integer',
        'km_percorrido' => 'integer',
        'checklist_saida' => 'array',
        'checklist_retorno' => 'array',
    ];

    public static function gerarProximoNumero(?int $ano = null): string
    {
        $ano = $ano ?? (int) date('Y');
        $ultimo = self::whereYear('data_saida', $ano)->count();
        return sprintf('VIA-%d/%05d', $ano, $ultimo + 1);
    }

    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }

    public function motorista(): BelongsTo
    {
        return $this->belongsTo(Motorista::class, 'motorista_id');
    }

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitante_user_id');
    }

    public function unidadeAdministrativa(): BelongsTo
    {
        return $this->belongsTo(UnidadeAdministrativa::class, 'unidade_administrativa_id');
    }

    public function processo(): BelongsTo
    {
        return $this->belongsTo(Processo::class, 'processo_id');
    }

    /**
     * Encerra a viagem registrando o retorno, calculando o km rodado
     * e atualizando o hodômetro atual do veículo.
     */
    public function registrarRetorno(int $kmRetorno, ?string $ocorrencias = null, ?array $checklistRetorno = null): void
    {
        if ($this->situacao !== 'em_andamento') {
            throw new \DomainException("A viagem {$this->numero} não pode ser concluída pois se encontra no status '{$this->situacao}'.");
        }

        if ($kmRetorno < $this->km_saida) {
            throw new \DomainException("O hodômetro de retorno ({$kmRetorno} km) não pode ser inferior ao hodômetro de saída ({$this->km_saida} km).");
        }

        DB::transaction(function () use ($kmRetorno, $ocorrencias, $checklistRetorno) {
            $kmPercorrido = max(0, $kmRetorno - $this->km_saida);

            $this->update([
                'km_retorno' => $kmRetorno,
                'km_percorrido' => $kmPercorrido,
                'data_retorno' => now(),
                'situacao' => 'concluida',
                'ocorrencias' => $ocorrencias,
                'checklist_retorno' => $checklistRetorno,
            ]);

            // Atualiza hodômetro do veículo e libera para disponível
            if ($this->veiculo) {
                $this->veiculo->update([
                    'km_atual' => max($this->veiculo->km_atual, $kmRetorno),
                    'status' => 'disponivel',
                ]);
            }
        });
    }

    public function getSituacaoFormatadaAttribute(): string
    {
        return match ($this->situacao) {
            'agendada' => 'Agendada',
            'em_andamento' => 'Em Trânsito / Em Andamento',
            'concluida' => 'Concluída / Retornou',
            'cancelada' => 'Cancelada',
            default => ucfirst(str_replace('_', ' ', $this->situacao)),
        };
    }

    public function getSituacaoBadgeAttribute(): string
    {
        return match ($this->situacao) {
            'agendada' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
            'em_andamento' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
            'concluida' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
            'cancelada' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
