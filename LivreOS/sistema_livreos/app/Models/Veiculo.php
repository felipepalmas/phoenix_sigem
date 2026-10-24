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

    // Helpers visuais
    public function getTipoFormatadoAttribute(): string
    {
        return match ($this->tipo) {
            'passeio' => 'Veículo Leve / Passeio',
            'utilitario' => 'Utilitário / Caminhonete',
            'caminhao' => 'Caminhão Pesado / Caçamba',
            'onibus_escolar' => 'Transporte Escolar (Ônibus/Van)',
            'ambulancia' => 'Ambulância / Saúde SAMU',
            'maquina_pesada' => 'Máquina Pesada (Patrola/Retro)',
            'motocicleta' => 'Motocicleta / Fiscalização',
            default => ucfirst(str_replace('_', ' ', $this->tipo)),
        };
    }

    public function getStatusFormatadoAttribute(): string
    {
        return match ($this->status) {
            'disponivel' => 'Disponível na Garagem',
            'em_viagem' => 'Em Viagem / Rota',
            'em_manutencao' => 'Em Manutenção / Oficina',
            'inoperante' => 'Inoperante / Aguardando Peça',
            'baixado' => 'Baixado / Leilão',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
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
