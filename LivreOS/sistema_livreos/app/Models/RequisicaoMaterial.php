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
                
                $maxSeq = 0;
                $todasReqsDoAno = self::where('ano', $ano)->pluck('numero');
                foreach ($todasReqsDoAno as $num) {
                    if (preg_match('/REQ-\d+\/(\d+)/', $num, $matches)) {
                        $seq = (int) $matches[1];
                        if ($seq > $maxSeq) {
                            $maxSeq = $seq;
                        }
                    }
                }
                $proximo = $maxSeq + 1;
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
     * Aprova a requisição pela chefia da unidade solicitante
     */
    public function aprovar(User $user, ?string $parecer = null): void
    {
        if ($this->situacao !== 'pendente_aprovacao') {
            throw new \DomainException("Esta requisição já foi processada (situação: '{$this->situacao_formatada}') e não pode ser aprovada novamente.");
        }

        // Segregação de funções: solicitante não pode aprovar o próprio pedido, exceto superadmin
        if ($this->solicitante_user_id === $user->id && ! $user->is_admin) {
            throw new \DomainException("Princípio da Segregação de Funções: O servidor solicitante não pode aprovar a sua própria requisição de materiais.");
        }

        $this->update([
            'situacao'             => 'aprovada',
            'aprovado_por_user_id' => $user->id,
            'aprovado_em'          => now(),
            'parecer_aprovacao'    => $parecer,
        ]);
    }

    /**
     * Recusa a requisição
     */
    public function recusar(User $user, string $motivo): void
    {
        if ($this->situacao !== 'pendente_aprovacao') {
            throw new \DomainException("Esta requisição já foi processada (situação: '{$this->situacao_formatada}').");
        }

        $this->update([
            'situacao'             => 'recusada',
            'aprovado_por_user_id' => $user->id,
            'aprovado_em'          => now(),
            'parecer_aprovacao'    => $motivo,
        ]);
    }

    /**
     * Atende a requisição e baixa os itens do estoque do almoxarifado
     * 
     * @param User $almoxarife
     * @param array $itensAtendidos [item_id => quantidade_atendida]
     * @param string|null $observacoes
     */
    public function atender(User $almoxarife, array $itensAtendidos, ?string $observacoes = null): void
    {
        if (! in_array($this->situacao, ['aprovada', 'atendida_parcial'])) {
            throw new \DomainException("A requisição precisa estar aprovada para ser atendida pelo almoxarifado (situação atual: '{$this->situacao_formatada}').");
        }

        DB::transaction(function () use ($almoxarife, $itensAtendidos, $observacoes) {
            $totalSolicitado = 0;
            $totalAtendido = 0;

            foreach ($this->itens as $item) {
                $solicitada = (float) $item->quantidade_solicitada;
                $totalSolicitado += $solicitada;

                $qtdAtender = isset($itensAtendidos[$item->id]) ? (float) $itensAtendidos[$item->id] : $solicitada;
                
                if ($qtdAtender > 0) {
                    if ($qtdAtender > $solicitada) {
                        throw new \DomainException("A quantidade atendida ({$qtdAtender}) não pode exceder a quantidade solicitada ({$solicitada}) para o item '{$item->produto?->nome}'.");
                    }

                    // Checa saldo do produto
                    $produto = Produto::lockForUpdate()->find($item->produto_id);
                    if ($produto && (float) $produto->estoque_quantidade < $qtdAtender) {
                        throw new \DomainException("Saldo insuficiente em estoque para o produto '{$produto->nome}'. Disponível: {$produto->estoque_quantidade}, Necessário: {$qtdAtender}.");
                    }

                    $item->quantidade_atendida = $qtdAtender;
                    $item->save();
                    $totalAtendido += $qtdAtender;

                    // Baixa no estoque
                    MovimentacaoEstoque::registrar(
                        produtoId: $item->produto_id,
                        depositoId: $this->almoxarifado_id,
                        tipo: 'saida',
                        quantidade: $qtdAtender,
                        origemTipo: 'requisicao',
                        origemId: $this->id,
                        documentoRef: $this->numero,
                        userId: $almoxarife->id,
                        justificativa: "Atendimento da Requisição {$this->numero} para {$this->unidadeSolicitante?->nome}"
                    );
                }
            }

            $novaSituacao = ($totalAtendido >= $totalSolicitado) ? 'atendida' : 'atendida_parcial';

            $this->update([
                'situacao'                => $novaSituacao,
                'atendido_por_user_id'    => $almoxarife->id,
                'atendido_em'             => now(),
                'observacoes_atendimento' => $observacoes,
            ]);
        });
    }

    /**
     * Confirma recebimento pelo solicitante
     */
    public function confirmarRecebimento(User $user): void
    {
        if (! in_array($this->situacao, ['atendida', 'atendida_parcial'])) {
            throw new \DomainException("Apenas requisições já atendidas ou separadas podem ter o recebimento confirmado.");
        }

        $this->update([
            'situacao'             => 'concluida',
            'recebido_por_user_id' => $user->id,
            'recebido_em'          => now(),
        ]);
    }
}
