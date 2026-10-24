<?php

/**
 * Componente da aplicação Phoenix - SIGEM (Zero Papel)
 * Modelo de Processo Administrativo e Protocolo Digital
 *
 * @license AGPL-3.0
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Processo extends Model
{
    use HasFactory;

    protected $table = 'processos';

    protected $fillable = [
        'numero',
        'ano',
        'sequencial',
        'interessado_id',
        'assunto',
        'descricao',
        'unidade_atual_id',
        'usuario_responsavel_id',
        'status',
        'prioridade',
        'sigilo',
        'prazo_dias',
        'data_limite',
        'data_conclusao',
        'created_by',
    ];

    protected $casts = [
        'ano' => 'integer',
        'sequencial' => 'integer',
        'prazo_dias' => 'integer',
        'data_limite' => 'date',
        'data_conclusao' => 'datetime',
    ];

    public static function gerarProximoNumero(?int $ano = null): array
    {
        $ano = $ano ?? (int) date('Y');
        return DB::transaction(function () use ($ano) {
            $ultimo = self::where('ano', $ano)->lockForUpdate()->max('sequencial') ?? 0;
            $proximo = $ultimo + 1;
            $numero = sprintf('%d/%06d', $ano, $proximo);
            return [
                'ano' => $ano,
                'sequencial' => $proximo,
                'numero' => $numero,
            ];
        });
    }

    public function podeSerTramitado(): bool
    {
        return $this->status === 'tramitando';
    }

    public function podeSerConcluido(): bool
    {
        return $this->status === 'tramitando';
    }

    public function interessado(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'interessado_id');
    }

    public function unidadeAtual(): BelongsTo
    {
        return $this->belongsTo(UnidadeAdministrativa::class, 'unidade_atual_id');
    }

    public function usuarioResponsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_responsavel_id');
    }

    public function criador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(ProcessoDocumento::class, 'processo_id')->orderBy('id');
    }

    public function tramitacoes(): HasMany
    {
        return $this->hasMany(Tramitacao::class, 'processo_id')->orderBy('id', 'desc');
    }

    /**
     * Tramitar processo para uma nova unidade administrativa com despacho.
     */
    public function tramitarPara(int $unidadeDestinoId, string $despacho, int $usuarioEnvioId, string $parecerTipo = 'informativo'): Tramitacao
    {
        if (! $this->podeSerTramitado()) {
            throw new \DomainException("Processo com status '{$this->status}' não permite novas tramitações.");
        }

        return DB::transaction(function () use ($unidadeDestinoId, $despacho, $usuarioEnvioId, $parecerTipo) {
            $origemId = $this->unidade_atual_id ?? $unidadeDestinoId;

            // Criar registro de tramitação
            $tramitacao = Tramitacao::create([
                'processo_id' => $this->id,
                'unidade_origem_id' => $origemId,
                'unidade_destino_id' => $unidadeDestinoId,
                'usuario_envio_id' => $usuarioEnvioId,
                'despacho' => $despacho,
                'parecer_tipo' => $parecerTipo,
                'status' => 'enviado',
                'data_envio' => now(),
                'created_by' => $usuarioEnvioId,
            ]);

            // Atualizar unidade atual e status do processo
            $this->update([
                'unidade_atual_id' => $unidadeDestinoId,
                'usuario_responsavel_id' => null, // Resetar para que o servidor da nova unidade receba
                'status' => 'tramitando',
            ]);

            return $tramitacao;
        });
    }

    /**
     * Receber o processo na unidade de destino pelo servidor logado.
     */
    public function receberNaUnidade(int $usuarioRecebimentoId): bool
    {
        return DB::transaction(function () use ($usuarioRecebimentoId) {
            $ultimaTramitacao = $this->tramitacoes()->where('status', 'enviado')->first();
            if ($ultimaTramitacao) {
                $ultimaTramitacao->update([
                    'usuario_recebimento_id' => $usuarioRecebimentoId,
                    'data_recebimento' => now(),
                    'status' => 'recebido',
                ]);
            }

            return $this->update([
                'usuario_responsavel_id' => $usuarioRecebimentoId,
            ]);
        });
    }

    /**
     * Concluir / Arquivar / Deferir processo
     */
    public function concluir(string $statusFinal, string $despachoFinal, int $usuarioId): Tramitacao
    {
        if (! $this->podeSerConcluido()) {
            throw new \DomainException("Processo com status '{$this->status}' já se encontra encerrado.");
        }

        $parecerCalculado = match ($statusFinal) {
            'deferido' => 'favoravel',
            'indeferido' => 'desfavoravel',
            'suspenso' => 'diligencia',
            default => 'informativo',
        };

        return DB::transaction(function () use ($statusFinal, $despachoFinal, $usuarioId, $parecerCalculado) {
            $tramitacao = Tramitacao::create([
                'processo_id' => $this->id,
                'unidade_origem_id' => $this->unidade_atual_id,
                'unidade_destino_id' => $this->unidade_atual_id,
                'usuario_envio_id' => $usuarioId,
                'usuario_recebimento_id' => $usuarioId,
                'despacho' => $despachoFinal,
                'parecer_tipo' => $parecerCalculado,
                'status' => 'concluido',
                'data_envio' => now(),
                'data_recebimento' => now(),
                'created_by' => $usuarioId,
            ]);

            $this->update([
                'status' => $statusFinal,
                'data_conclusao' => now(),
            ]);

            return $tramitacao;
        });
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'tramitando' => ['bg' => 'bg-blue-100 dark:bg-blue-900/30', 'text' => 'text-blue-800 dark:text-blue-300', 'label' => 'Em Tramitação'],
            'deferido'   => ['bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'text' => 'text-emerald-800 dark:text-emerald-300', 'label' => 'Deferido'],
            'indeferido' => ['bg' => 'bg-rose-100 dark:bg-rose-900/30', 'text' => 'text-rose-800 dark:text-rose-300', 'label' => 'Indeferido'],
            'arquivado'  => ['bg' => 'bg-gray-100 dark:bg-gray-800', 'text' => 'text-gray-700 dark:text-gray-300', 'label' => 'Arquivado'],
            'suspenso'   => ['bg' => 'bg-amber-100 dark:bg-amber-900/30', 'text' => 'text-amber-800 dark:text-amber-300', 'label' => 'Suspenso / Diligência'],
            default      => ['bg' => 'bg-gray-100 dark:bg-gray-800', 'text' => 'text-gray-700 dark:text-gray-300', 'label' => ucfirst($this->status)],
        };
    }

    public function getPrioridadeBadgeAttribute(): array
    {
        return match ($this->prioridade) {
            'urgente' => ['bg' => 'bg-red-100 dark:bg-red-900/30', 'text' => 'text-red-700 dark:text-red-300', 'label' => 'Urgente'],
            'normal'  => ['bg' => 'bg-slate-100 dark:bg-slate-800', 'text' => 'text-slate-700 dark:text-slate-300', 'label' => 'Normal'],
            'baixa'   => ['bg' => 'bg-gray-100 dark:bg-gray-800', 'text' => 'text-gray-600 dark:text-gray-400', 'label' => 'Baixa'],
            default   => ['bg' => 'bg-gray-100 dark:bg-gray-800', 'text' => 'text-gray-600 dark:text-gray-400', 'label' => ucfirst($this->prioridade)],
        };
    }
}
