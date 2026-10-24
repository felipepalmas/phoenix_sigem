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
use Illuminate\Support\Facades\DB;

class RequisicaoMaterial extends Model
{
    use HasFactory;

    protected $table = 'requisicoes_materiais';

    protected $fillable = [
        'numero',
        'ano',
        'unidade_solicitante_id',
        'almoxarifado_id',
        'solicitante_user_id',
        'situacao',
        'prioridade',
        'justificativa',
        'aprovado_por_user_id',
        'aprovado_em',
        'parecer_aprovacao',
        'atendido_por_user_id',
        'atendido_em',
        'observacoes_atendimento',
        'recebido_por_user_id',
        'recebido_em',
        'processo_id',
    ];

    protected $casts = [
        'ano'          => 'integer',
        'aprovado_em'  => 'datetime',
        'atendido_em'  => 'datetime',
        'recebido_em'  => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($requisicao) {
            if (empty($requisicao->numero)) {
                $ano = $requisicao->ano ?: (int) date('Y');
                $requisicao->ano = $ano;
                
                $proximo = \App\Support\Numerador::proximo('REQ', $ano, fn (int $a) => \App\Support\Numerador::maiorSufixo('requisicoes_materiais', 'numero', "REQ-{$a}/"));
                $requisicao->numero = sprintf('REQ-%04d/%05d', $ano, $proximo);
            }
        });
    }

    public function unidadeSolicitante()
    {
        return $this->belongsTo(UnidadeAdministrativa::class, 'unidade_solicitante_id');
    }

    public function almoxarifado()
    {
        return $this->belongsTo(Deposito::class, 'almoxarifado_id');
    }

    public function solicitante()
    {
        return $this->belongsTo(User::class, 'solicitante_user_id');
    }

    public function aprovadoPor()
    {
        return $this->belongsTo(User::class, 'aprovado_por_user_id');
    }

    public function atendidoPor()
    {
        return $this->belongsTo(User::class, 'atendido_por_user_id');
    }

    public function recebidoPor()
    {
        return $this->belongsTo(User::class, 'recebido_por_user_id');
    }

    public function processo()
    {
        return $this->belongsTo(Processo::class, 'processo_id');
    }

    public function itens()
    {
        return $this->hasMany(RequisicaoMaterialItem::class, 'requisicao_id');
    }

    // Helpers de status / badge
    public function getSituacaoFormatadaAttribute(): string
    {
        return match ($this->situacao) {
            'rascunho'           => 'Rascunho',
            'pendente_aprovacao' => 'Aguardando Aprovação',
            'aprovada'           => 'Aprovada / No Almoxarifado',
            'atendida_parcial'   => 'Atendida Parcialmente',
            'atendida'           => 'Atendida / Separada',
            'concluida'          => 'Recebimento Confirmado',
            'recusada'           => 'Recusada',
            'cancelada'          => 'Cancelada',
            default              => ucfirst(str_replace('_', ' ', $this->situacao)),
        };
    }

    public function getBadgeClassAttribute(): string
    {
        return match ($this->situacao) {
            'pendente_aprovacao' => 'badge-warning text-yellow-800 bg-yellow-100 dark:bg-yellow-900/30 dark:text-yellow-400',
            'aprovada'           => 'badge-primary text-blue-800 bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400',
            'atendida_parcial'   => 'badge-info text-cyan-800 bg-cyan-100 dark:bg-cyan-900/30 dark:text-cyan-400',
            'atendida'           => 'badge-success text-emerald-800 bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400',
            'concluida'          => 'badge-success text-green-800 bg-green-100 dark:bg-green-900/30 dark:text-green-400',
            'recusada', 'cancelada' => 'badge-danger text-red-800 bg-red-100 dark:bg-red-900/30 dark:text-red-400',
            default              => 'badge-secondary text-gray-700 bg-gray-100 dark:bg-gray-800 dark:text-gray-300',
        };
    }

    /**
     * Unidade administrativa dona do almoxarifado (depósito) desta requisição.
     */
    protected function unidadeDoAlmoxarifado(): ?int
    {
        return $this->almoxarifado?->unidade_administrativa_id;
    }

    public function podeSerAprovadaPor(?User $user): bool
    {
        return $user !== null
            && $this->situacao === 'pendente_aprovacao'
            && ((int) $this->solicitante_user_id !== (int) $user->id || $user->is_admin)
            && $user->podeNaUnidade('almoxarifado.aprovar', $this->unidade_solicitante_id);
    }

    public function podeSerAtendidaPor(?User $user): bool
    {
        if ($user === null || ! in_array($this->situacao, ['aprovada', 'atendida_parcial'], true)) {
            return false;
        }
        $unidade = $this->unidadeDoAlmoxarifado();
        if ($unidade === null) {
            return $user->ehGestorGeral() || $user->hasPermission('almoxarifado.atender');
        }

        return $user->podeNaUnidade('almoxarifado.atender', $unidade);
    }

    public function podeTerRecebimentoConfirmadoPor(?User $user): bool
    {
        return $user !== null
            && in_array($this->situacao, ['atendida', 'atendida_parcial'], true)
            && ((int) $this->solicitante_user_id === (int) $user->id
                || $user->lotadoEm($this->unidade_solicitante_id)
                || $user->ehGestorGeral());
    }

    /**
     * Aprova a requisição (chefia da secretaria solicitante).
     */
    public function aprovar(User $user, ?string $parecer = null): void
    {
        if ($this->situacao !== 'pendente_aprovacao') {
            throw new \DomainException("Esta requisição já foi processada (situação: '{$this->situacao_formatada}') e não pode ser aprovada novamente.");
        }
        if ((int) $this->solicitante_user_id === (int) $user->id && ! $user->is_admin) {
            throw new \DomainException('Segregação de funções: o servidor solicitante não pode aprovar a própria requisição.');
        }
        if (! $user->podeNaUnidade('almoxarifado.aprovar', $this->unidade_solicitante_id)) {
            throw new \DomainException('Somente a chefia da secretaria solicitante (lotada nela e com permissão de aprovar) pode aprovar esta requisição.');
        }

        $this->update([
            'situacao'             => 'aprovada',
            'aprovado_por_user_id' => $user->id,
            'aprovado_em'          => now(),
            'parecer_aprovacao'    => $parecer,
        ]);
    }

    /**
     * Recusa a requisição.
     */
    public function recusar(User $user, string $motivo): void
    {
        if ($this->situacao !== 'pendente_aprovacao') {
            throw new \DomainException("Esta requisição já foi processada (situação: '{$this->situacao_formatada}').");
        }
        if (! $user->podeNaUnidade('almoxarifado.aprovar', $this->unidade_solicitante_id)) {
            throw new \DomainException('Somente a chefia da secretaria solicitante pode recusar esta requisição.');
        }

        $this->update([
            'situacao'             => 'recusada',
            'aprovado_por_user_id' => $user->id,
            'aprovado_em'          => now(),
            'parecer_aprovacao'    => $motivo,
        ]);
    }

    /**
     * Quantidade ainda pendente de entrega para um item.
     */
    public static function pendenteDoItem(RequisicaoMaterialItem $item): float
    {
        return max(0, round((float) $item->quantidade_solicitada - (float) $item->quantidade_atendida, 6));
    }

    /**
     * Atende (total ou parcialmente) e baixa os itens do estoque.
     *
     * Cada atendimento entrega no máximo o que AINDA FALTA de cada item; a quantidade
     * atendida é acumulada. Um item não informado não é entregue nesta rodada.
     *
     * @param  array  $itensAtendidos  [item_id => quantidade entregue AGORA]
     */
    public function atender(User $almoxarife, array $itensAtendidos, ?string $observacoes = null): void
    {
        if (! in_array($this->situacao, ['aprovada', 'atendida_parcial'], true)) {
            throw new \DomainException("A requisição precisa estar aprovada para ser atendida (situação atual: '{$this->situacao_formatada}').");
        }
        if (! $this->podeSerAtendidaPor($almoxarife)) {
            throw new \DomainException('Somente o almoxarife da secretaria dona deste almoxarifado pode atender a requisição.');
        }

        DB::transaction(function () use ($almoxarife, $itensAtendidos, $observacoes) {
            $entregueAgora = 0.0;
            $aindaFalta = 0.0;

            foreach ($this->itens()->lockForUpdate()->get() as $item) {
                $pendente = self::pendenteDoItem($item);
                $qtd = isset($itensAtendidos[$item->id]) ? round((float) $itensAtendidos[$item->id], 6) : 0.0;

                if ($qtd < 0) {
                    throw new \DomainException("Quantidade inválida para o item '{$item->produto?->nome}'.");
                }
                if ($qtd > $pendente + 0.000001) {
                    throw new \DomainException("O item '{$item->produto?->nome}' tem apenas {$pendente} pendente(s); não é possível entregar {$qtd}.");
                }

                if ($qtd > 0) {
                    // MovimentacaoEstoque::registrar trava o produto e recusa saldo insuficiente
                    MovimentacaoEstoque::registrar(
                        produtoId: $item->produto_id,
                        depositoId: $this->almoxarifado_id,
                        tipo: 'saida',
                        quantidade: $qtd,
                        origemTipo: 'requisicao',
                        origemId: $this->id,
                        documentoRef: $this->numero,
                        userId: $almoxarife->id,
                        justificativa: "Atendimento da Requisição {$this->numero} para {$this->unidadeSolicitante?->nome}"
                    );

                    $item->quantidade_atendida = round((float) $item->quantidade_atendida + $qtd, 6);
                    $item->save();
                    $entregueAgora += $qtd;
                }

                $aindaFalta += self::pendenteDoItem($item);
            }

            if ($entregueAgora <= 0) {
                throw new \DomainException('Informe a quantidade entregue de pelo menos um item.');
            }

            $this->update([
                'situacao'                => $aindaFalta > 0 ? 'atendida_parcial' : 'atendida',
                'atendido_por_user_id'    => $almoxarife->id,
                'atendido_em'             => now(),
                'observacoes_atendimento' => $observacoes,
            ]);
        });
    }

    /**
     * Confirma o recebimento pelo solicitante (ou pela secretaria solicitante).
     */
    public function confirmarRecebimento(User $user): void
    {
        if (! in_array($this->situacao, ['atendida', 'atendida_parcial'], true)) {
            throw new \DomainException('Apenas requisições já atendidas podem ter o recebimento confirmado.');
        }
        if (! $this->podeTerRecebimentoConfirmadoPor($user)) {
            throw new \DomainException('Somente o solicitante ou um servidor da secretaria solicitante pode confirmar o recebimento.');
        }

        $this->update([
            'situacao'             => 'concluida',
            'recebido_por_user_id' => $user->id,
            'recebido_em'          => now(),
        ]);
    }
}
