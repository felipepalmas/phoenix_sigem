<?php

/**
 * Componente da aplicação Phoenix - SIGEM
 * Trilha de Auditoria para Processos Sigilosos e Restritos (LAI / LGPD)
 *
 * @license AGPL-3.0
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditProcessoSigiloso extends Model
{
    use HasFactory;

    protected $table = 'audit_processos_sigilosos';

    protected $fillable = [
        'processo_id',
        'user_id',
        'unidade_id',
        'ip_address',
        'user_agent',
        'acao',
        'justificativa',
    ];

    public function processo()
    {
        return $this->belongsTo(Processo::class, 'processo_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function unidade()
    {
        return $this->belongsTo(UnidadeAdministrativa::class, 'unidade_id');
    }
}
