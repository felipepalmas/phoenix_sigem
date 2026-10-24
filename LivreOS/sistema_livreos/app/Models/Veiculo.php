<?php

/**
 * Componente da aplicação Phoenix - SIGEM
 * Modelo: Veículo e Máquina da Frota Municipal
 *
 * @license AGPL-3.0
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Veiculo extends Model
{
    use HasFactory;

    protected $table = 'veiculos';

    protected $fillable = [
        'placa',
        'modelo',
        'marca',
        'ano_fabricacao',
        'ano_modelo',
        'tipo',
        'combustivel',
        'renavam',
        'chassi',
        'patrimonio_tombamento',
        'unidade_administrativa_id',
        'motorista_responsavel_id',
        'km_atual',
        'status',
        'capacidade_tanque',
        'capacidade_passageiros',
        'foto_path',
        'observacoes',
        'ativo',
    ];

    protected $casts = [
        'ano_fabricacao' => 'integer',
        'ano_modelo' => 'integer',
        'km_atual' => 'integer',
        'capacidade_tanque' => 'decimal:2',
        'capacidade_passageiros' => 'integer',
        'ativo' => 'boolean',
    ];

    public function unidadeAdministrativa(): BelongsTo
    {
        return $this->belongsTo(UnidadeAdministrativa::class, 'unidade_administrativa_id');
    }

    public function motoristaResponsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'motorista_responsavel_id');
    }

    public function viagens(): HasMany
    {
        return $this->hasMany(Viagem::class, 'veiculo_id');
    }

    public function abastecimentos(): HasMany
    {
        return $this->hasMany(Abastecimento::class, 'veiculo_id');
    }

    public function manutencoes(): HasMany
    {
        return $this->hasMany(ManutencaoFrota::class, 'veiculo_id');
    }

    /** Tipos aceitos no cadastro (valor gravado => rótulo). */
    public const TIPOS = [
        'passeio' => 'Passeio',
        'utilitario' => 'Utilitário / caminhonete',
        'caminhao' => 'Caminhão',
        'onibus_escolar' => 'Transporte escolar (ônibus/van)',
        'ambulancia' => 'Ambulância',
        'maquina_pesada' => 'Máquina pesada',
        'motocicleta' => 'Motocicleta',
    ];

    /** Combustíveis aceitos (valor gravado => rótulo). */
    public const COMBUSTIVEIS = [
        'diesel_s10' => 'Diesel S10',
        'diesel_s500' => 'Diesel S500',
        'gasolina' => 'Gasolina',
        'etanol' => 'Etanol',
        'flex' => 'Flex (gasolina/etanol)',
        'gnv' => 'GNV',
        'eletrico' => 'Elétrico',
    ];

    /** Situações (valor gravado => rótulo). */
    public const SITUACOES = [
        'disponivel' => 'Disponível',
        'em_viagem' => 'Em viagem',
        'em_manutencao' => 'Em manutenção',
        'inoperante' => 'Inoperante',
        'baixado' => 'Baixado',
    ];

    // Helpers visuais
    public function getTipoFormatadoAttribute(): string
    {
        return self::TIPOS[$this->tipo] ?? ucfirst(str_replace('_', ' ', (string) $this->tipo));
    }

    public function getCombustivelFormatadoAttribute(): string
    {
        return self::rotuloCombustivel($this->combustivel);
    }

    public static function rotuloCombustivel(?string $combustivel): string
    {
        return self::COMBUSTIVEIS[$combustivel] ?? ucfirst(str_replace('_', ' ', (string) $combustivel));
    }

    public function getStatusFormatadoAttribute(): string
    {
        return self::SITUACOES[$this->status] ?? ucfirst(str_replace('_', ' ', (string) $this->status));
    }

    /** Tom do badge SIGEM (info|ok|warn|danger|neutral). */
    public function getStatusTomAttribute(): string
    {
        return match ($this->status) {
            'disponivel' => 'ok',
            'em_viagem' => 'info',
            'em_manutencao' => 'warn',
            'inoperante' => 'danger',
            default => 'neutral',
        };
    }

    /** Máquinas pesadas registram horímetro (horas de uso), não quilometragem. */
    public function getUsaHorimetroAttribute(): bool
    {
        return $this->tipo === 'maquina_pesada';
    }

    public function getUnidadeMedidaAttribute(): string
    {
        return $this->usa_horimetro ? 'h' : 'km';
    }

    public function getHodometroFormatadoAttribute(): string
    {
        return number_format((int) $this->km_atual, 0, ',', '.') . ' ' . $this->unidade_medida;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'disponivel' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
            'em_viagem' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
            'em_manutencao' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
            'inoperante' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300',
            'baixado' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
