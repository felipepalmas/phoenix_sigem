<?php

/**
 * Componente da aplicação Phoenix - SIGEM (Zero Papel)
 * Modelo de Unidades Administrativas (Secretarias, Departamentos, Setores)
 *
 * @license AGPL-3.0
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnidadeAdministrativa extends Model
{
    use HasFactory;

    protected $table = 'unidades_administrativas';

    protected $fillable = [
        'parent_id',
        'nome',
        'sigla',
        'tipo',
        'responsavel_id',
        'email',
        'telefone',
        'ativo',
        'ordem',
        'created_by',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'ordem' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(UnidadeAdministrativa::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(UnidadeAdministrativa::class, 'parent_id')->orderBy('ordem')->orderBy('nome');
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }

    public function processosAtuais(): HasMany
    {
        return $this->hasMany(Processo::class, 'unidade_atual_id');
    }

    public function getNomeCompletoAttribute(): string
    {
        return $this->sigla ? "{$this->sigla} - {$this->nome}" : $this->nome;
    }
}
