<?php

/**
 * Configuração da Integração com o Login Único gov.br (OpenID Connect / OAuth2)
 * Componente da aplicação Phoenix - SIGEM
 *
 * Em conformidade com a Portaria SEDGG/ME nº 2.154/2021 e Decreto Federal nº 8.936/2016.
 *
 * @license AGPL-3.0
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Ativação da Integração gov.br
    |--------------------------------------------------------------------------
    | Define se a autenticação e assinatura eletrônica avançada do gov.br
    | estão habilitadas no sistema municipal.
    */
    'enabled' => env('GOVBR_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Ambiente gov.br (staging / production)
    |--------------------------------------------------------------------------
    | Use 'staging' durante o período de homologação e credenciamento oficial
    | junto ao Ministério da Gestão e Inovação em Serviços Públicos (MGI).
    */
    'environment' => env('GOVBR_ENVIRONMENT', 'staging'),

    /*
    |--------------------------------------------------------------------------
    | Credenciais do Cliente OAuth2 Municipal
    |--------------------------------------------------------------------------
    | Obtidas após adesão formal e aprovação do plano de integração do município.
    */
    'client_id' => env('GOVBR_CLIENT_ID', ''),
    'client_secret' => env('GOVBR_CLIENT_SECRET', ''),
    'redirect_uri' => env('GOVBR_REDIRECT_URI', env('APP_URL', 'http://127.0.0.1:8000') . '/auth/govbr/callback'),

    /*
    |--------------------------------------------------------------------------
    | Escopos Solicitados
    |--------------------------------------------------------------------------
    | - openid: Requerido pelo padrão OpenID Connect
    | - email: Acesso ao e-mail cadastrado
    | - profile: Nome completo e identificador do cidadão (CPF)
    | - govbr_confiabilidades: Consulta dos selos Bronze, Prata ou Ouro
    */
    'scopes' => ['openid', 'email', 'profile', 'govbr_confiabilidades'],

    /*
    |--------------------------------------------------------------------------
    | Endpoints Oficiais do gov.br
    |--------------------------------------------------------------------------
    */
    'endpoints' => [
        'staging' => [
            'authorize' => 'https://sso.staging.acesso.gov.br/authorize',
            'token'     => 'https://sso.staging.acesso.gov.br/token',
            'userinfo'  => 'https://sso.staging.acesso.gov.br/userinfo',
            'jwks'      => 'https://sso.staging.acesso.gov.br/jwk',
            'logout'    => 'https://sso.staging.acesso.gov.br/logout',
            'confiabilidade' => 'https://api.staging.acesso.gov.br/confiabilidades/v3/contas/',
        ],
        'production' => [
            'authorize' => 'https://sso.acesso.gov.br/authorize',
            'token'     => 'https://sso.acesso.gov.br/token',
            'userinfo'  => 'https://sso.acesso.gov.br/userinfo',
            'jwks'      => 'https://sso.acesso.gov.br/jwk',
            'logout'    => 'https://sso.acesso.gov.br/logout',
            'confiabilidade' => 'https://api.acesso.gov.br/confiabilidades/v3/contas/',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Níveis Mínimos de Confiabilidade para Assinatura Avançada
    |--------------------------------------------------------------------------
    | Pela Lei Federal 14.063/2020, atos oficiais com assinatura avançada
    | exigem, no mínimo, nível Prata (bancos conveniados) ou Ouro (biometria TSE).
    */
    'niveis_assinatura' => [
        'simples'  => ['BRONZE', 'PRATA', 'OURO'],
        'avancada' => ['PRATA', 'OURO'],
    ],
];
