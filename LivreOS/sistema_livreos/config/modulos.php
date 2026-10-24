<?php

/**
 * Configuração de Módulos - Phoenix SIGEM
 *
 * Em ambiente público municipal, módulos puramente comerciais
 * e financeiros privados são desativados por padrão.
 */

return [
    'comercial' => env('MODULO_COMERCIAL_ATIVO', false),
    'financeiro' => env('MODULO_FINANCEIRO_ATIVO', false),
    'vendas' => env('MODULO_VENDAS_ATIVO', false),
    'garantias' => env('MODULO_GARANTIAS_ATIVO', false),
];
