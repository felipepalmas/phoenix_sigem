<?php

/**
 * Componente da aplicação Phoenix - SIGEM (Zero Papel)
 * Modelo de Documentos e Peças do Processo Administrativo
 *
 * @license AGPL-3.0
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessoDocumento extends Model
{
    use HasFactory;

    protected $table = 'processo_documentos';

    protected $fillable = [
        'processo_id',
        'titulo',
        'tipo_documento',
        'caminho_arquivo',
        'nome_original',
        'tamanho_bytes',
        'mime_type',
        'hash_sha256',
        'assinado_digitalmente',
        'assinado_por_id',
        'data_assinatura',
        'created_by',
    ];

    protected $casts = [
        'tamanho_bytes' => 'integer',
        'assinado_digitalmente' => 'boolean',
        'data_assinatura' => 'datetime',
    ];

    public function processo(): BelongsTo
    {
        return $this->belongsTo(Processo::class, 'processo_id');
    }

    public function assinadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assinado_por_id');
    }

    public function criador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTamanhoFormatadoAttribute(): string
    {
        $bytes = $this->tamanho_bytes ?? 0;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }
}
