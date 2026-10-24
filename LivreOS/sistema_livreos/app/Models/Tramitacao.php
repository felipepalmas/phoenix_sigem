<?php

/**
 * Componente da aplicação Phoenix - SIGEM (Zero Papel)
 * Modelo de Tramitações e Despachos do Motor de Protocolo
 *
 * @license AGPL-3.0
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tramitacao extends Model
{
    use HasFactory;

    protected $table = 'tramitacoes';

    protected $fillable = [
        'processo_id',
        'unidade_origem_id',
        'unidade_destino_id',
        'usuario_envio_id',
        'usuario_recebimento_id',
        'despacho',
        'parecer_tipo',
        'status',
        'data_envio',
        'data_recebimento',
        'created_by',
    ];

    protected $casts = [
        'data_envio' => 'datetime',
        'data_recebimento' => 'datetime',
    ];

    public function processo(): BelongsTo
    {
        return $this->belongsTo(Processo::class, 'processo_id');
    }

    public function unidadeOrigem(): BelongsTo
    {
        return $this->belongsTo(UnidadeAdministrativa::class, 'unidade_origem_id');
    }

    public function unidadeDestino(): BelongsTo
    {
        return $this->belongsTo(UnidadeAdministrativa::class, 'unidade_destino_id');
    }

    public function usuarioEnvio(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_envio_id');
    }

    public function usuarioRecebimento(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_recebimento_id');
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'enviado' => ['bg' => 'bg-amber-100 dark:bg-amber-900/30', 'text' => 'text-amber-800 dark:text-amber-300', 'label' => 'Aguardando Recebimento'],
            'recebido' => ['bg' => 'bg-blue-100 dark:bg-blue-900/30', 'text' => 'text-blue-800 dark:text-blue-300', 'label' => 'Recebido / Em Análise'],
            'concluido' => ['bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'text' => 'text-emerald-800 dark:text-emerald-300', 'label' => 'Despachado / Concluído'],
            default => ['bg' => 'bg-gray-100 dark:bg-gray-800', 'text' => 'text-gray-700 dark:text-gray-300', 'label' => ucfirst($this->status)],
        };
    }
}
