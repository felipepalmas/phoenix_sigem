<?php

/**
 * Componente da aplicação Phoenix - SIGEM
 *
 * @license AGPL-3.0
 */

namespace App\Support;

/**
 * Vocabulário único de situações da Zeladoria Urbana.
 * Também reconhece os textos antigos gravados antes da padronização.
 */
class StatusZeladoria
{
    public const REGISTRADA = 'Registrada / Triagem';
    public const PROGRAMADA = 'Programada';
    public const EM_EXECUCAO = 'Em execução';
    public const AGUARDANDO_MATERIAL = 'Aguardando material';
    public const CONCLUIDA = 'Concluída / Executada';
    public const CANCELADA = 'Cancelada';
    public const IMPROCEDENTE = 'Improcedente';

    /** Situações que o servidor pode escolher manualmente (a conclusão tem tela própria). */
    public const ANDAMENTO = [self::PROGRAMADA, self::EM_EXECUCAO, self::AGUARDANDO_MATERIAL, self::CANCELADA, self::IMPROCEDENTE];

    /** Textos (antigos e atuais) de cada grupo, para contagens e filtros. */
    public const ABERTAS = ['Registrada / Triagem', 'Registrada', 'Em triagem', 'Aberta', 'Aberto'];
    public const EM_CAMPO = ['Programada', 'Em execução', 'Em andamento', 'Aguardando material'];
    public const ENCERRADAS = ['Concluída / Executada', 'Concluída', 'Concluida', 'Encerrada', 'Encerrado', 'Cancelada', 'Improcedente'];
    public const CONCLUIDAS = ['Concluída / Executada', 'Concluída', 'Concluida', 'Encerrada', 'Encerrado'];

    public static function encerrada(?string $status): bool
    {
        return in_array((string) $status, self::ENCERRADAS, true);
    }

    /** Rótulo curto para exibição. */
    public static function rotulo(?string $status): string
    {
        return match (true) {
            in_array($status, self::ABERTAS, true) => 'Em triagem',
            $status === 'Em andamento' => 'Em execução',
            in_array($status, self::CONCLUIDAS, true) => 'Concluída',
            default => (string) ($status ?: '—'),
        };
    }

    /** Tom do badge: info | ok | warn | danger | neutral */
    public static function tom(?string $status): string
    {
        return match (true) {
            in_array($status, self::CONCLUIDAS, true) => 'ok',
            $status === self::AGUARDANDO_MATERIAL => 'warn',
            in_array($status, [self::CANCELADA, self::IMPROCEDENTE], true) => 'neutral',
            default => 'info',
        };
    }
}
