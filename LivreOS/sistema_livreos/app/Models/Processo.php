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
        'chave_acesso',
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

    protected static function booted(): void
    {
        static::creating(function (Processo $processo) {
            if (empty($processo->chave_acesso)) {
                $processo->chave_acesso = self::novaChaveAcesso();
            }
        });
    }

    /** Chave de 8 caracteres sem letras ambíguas (0/O, 1/I/L), impressa no comprovante. */
    public static function novaChaveAcesso(): string
    {
        $alfabeto = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
        $chave = '';
        for ($i = 0; $i < 8; $i++) {
            $chave .= $alfabeto[random_int(0, strlen($alfabeto) - 1)];
        }

        return $chave;
    }

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

    /**
     * Processos que o usuário pode ver: públicos, os da unidade em que está lotado, os que autuou;
     * gestor geral e quem tem protocolo.sigiloso veem todos.
     */
    public function scopeVisiveisPara($query, ?User $user)
    {
        if ($user && ($user->ehGestorGeral() || $user->hasPermission('protocolo.sigiloso'))) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->where('sigilo', 'publico');
            if ($user) {
                $q->orWhere('created_by', $user->id);
                if ($user->unidade_administrativa_id) {
                    $q->orWhere('unidade_atual_id', $user->unidade_administrativa_id);
                }
            }
        });
    }

    public function visivelPara(?User $user): bool
    {
        if ($this->sigilo === 'publico') {
            return true;
        }
        if (! $user) {
            return false;
        }

        return $user->ehGestorGeral()
            || $user->hasPermission('protocolo.sigiloso')
            || (int) $this->created_by === (int) $user->id
            || $user->lotadoEm($this->unidade_atual_id);
    }

    /** Assunto para exibição: oculta o de processo restrito/sigiloso a quem não pode vê-lo. */
    public function assuntoPara(?User $user): string
    {
        return $this->visivelPara($user) ? (string) $this->assunto : 'Processo ' . mb_strtolower($this->sigilo_rotulo);
    }

    /** Situações finais (o processo não volta a tramitar). */
    public const ENCERRADOS = ['deferido', 'indeferido', 'arquivado', 'concluido'];

    public function podeSerTramitado(): bool
    {
        // Suspenso pode ser retomado tramitando (ex.: diligência cumprida)
        return in_array($this->status, ['tramitando', 'suspenso'], true);
    }

    public function estaEncerrado(): bool
    {
        return in_array($this->status, self::ENCERRADOS, true);
    }

    /** Última tramitação enviada e ainda não recebida na unidade atual. */
    public function tramitacaoPendente(): ?Tramitacao
    {
        return $this->tramitacoes()
            ->where('status', 'enviado')
            ->where('unidade_destino_id', $this->unidade_atual_id)
            ->first();
    }

    public function aguardaRecebimento(): bool
    {
        return ! $this->estaEncerrado() && $this->tramitacaoPendente() !== null;
    }

    public function getStatusRotuloAttribute(): string
    {
        return match ($this->status) {
            'tramitando' => 'Em tramitação',
            'deferido' => 'Deferido',
            'indeferido' => 'Indeferido',
            'arquivado' => 'Arquivado',
            'suspenso' => 'Suspenso',
            'concluido' => 'Concluído',
            default => ucfirst((string) $this->status),
        };
    }

    /** Tom do badge: info | ok | warn | danger | neutral */
    public function getStatusTomAttribute(): string
    {
        return match ($this->status) {
            'deferido', 'concluido' => 'ok',
            'indeferido' => 'danger',
            'suspenso' => 'warn',
            'arquivado' => 'neutral',
            default => 'info',
        };
    }

    public function getSigiloRotuloAttribute(): string
    {
        return match ($this->sigilo) {
            'publico' => 'Público',
            'restrito' => 'Restrito',
            'sigiloso' => 'Sigiloso',
            default => ucfirst((string) $this->sigilo),
        };
    }

    public function getPrioridadeRotuloAttribute(): string
    {
        return match ($this->prioridade) {
            'urgente' => 'Urgente',
            'baixa' => 'Baixa',
            default => 'Normal',
        };
    }

    /** Dias até o prazo (negativo = vencido); null quando não há prazo ou já encerrou. */
    public function getDiasParaPrazoAttribute(): ?int
    {
        if (! $this->data_limite || $this->estaEncerrado()) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->data_limite->copy()->startOfDay(), false);
    }

    public function podeSerConcluido(): bool
    {
        return in_array($this->status, ['tramitando', 'suspenso'], true);
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
            $ultimaTramitacao = $this->tramitacaoPendente();
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
