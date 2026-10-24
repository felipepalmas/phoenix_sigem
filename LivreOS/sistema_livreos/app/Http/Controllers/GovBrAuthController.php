<?php

/**
 * Componente da aplicação Phoenix - SIGEM
 * Controlador de Autenticação Federada gov.br (OAuth2 / OpenID Connect)
 *
 * @license AGPL-3.0
 */

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GovBrAuthController extends Controller
{
    /**
     * Redireciona o usuário para o Servidor de Autorização do gov.br.
     */
    public function redirect(Request $request)
    {
        $enabled = config('govbr.enabled', false);
        $clientId = config('govbr.client_id');

        // Se a integração ainda não estiver com as chaves oficiais preenchidas no .env
        if (! $enabled || empty($clientId)) {
            return redirect()->route('login')->with('warning', 
                'A integração com o Login Único gov.br está aguardando homologação das credenciais oficiais junto ao Ministério da Gestão e Inovação (MGI). Consulte o guia "GOVBR-INTEGRACAO.md" ou utilize seu usuário e senha institucionais.');
        }

        $env = config('govbr.environment', 'staging');
        $endpoint = config("govbr.endpoints.{$env}.authorize");
        $redirectUri = config('govbr.redirect_uri');
        $scopes = implode(' ', config('govbr.scopes', ['openid', 'email', 'profile']));

        $state = Str::random(40);
        $nonce = Str::random(40);

        // Geração do PKCE (RFC 7636 / Requisito Obrigatório gov.br)
        $codeVerifier = Str::random(64);
        $codeChallenge = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');

        session([
            'govbr_oauth_state' => $state,
            'govbr_oauth_nonce' => $nonce,
            'govbr_code_verifier' => $codeVerifier,
            'govbr_intended_url' => url()->previous(),
        ]);

        $query = http_build_query([
            'response_type'         => 'code',
            'client_id'             => $clientId,
            'scope'                 => $scopes,
            'redirect_uri'          => $redirectUri,
            'state'                 => $state,
            'nonce'                 => $nonce,
            'code_challenge'        => $codeChallenge,
            'code_challenge_method' => 'S256',
        ]);

        return redirect()->away("{$endpoint}?{$query}");
    }

    /**
     * Recebe o callback do gov.br com o authorization code e efetua o login.
     */
    public function callback(Request $request)
    {
        $savedState = session('govbr_oauth_state');
        $savedNonce = session('govbr_oauth_nonce');
        $codeVerifier = session('govbr_code_verifier');

        $state = $request->input('state');
        $code = $request->input('code');

        if (empty($code) || empty($state) || $state !== $savedState) {
            return redirect()->route('login')->with('error', 'Falha na validação de segurança da autenticação gov.br (estado inválido ou requisição cancelada).');
        }

        $env = config('govbr.environment', 'staging');
        $tokenEndpoint = config("govbr.endpoints.{$env}.token");
        $clientId = config('govbr.client_id');
        $clientSecret = config('govbr.client_secret');
        $redirectUri = config('govbr.redirect_uri');

        try {
            $tokenParams = [
                'grant_type'    => 'authorization_code',
                'code'          => $code,
                'redirect_uri'  => $redirectUri,
            ];

            if ($codeVerifier) {
                $tokenParams['code_verifier'] = $codeVerifier;
            }

            $response = Http::asForm()
                ->withBasicAuth($clientId, $clientSecret)
                ->post($tokenEndpoint, $tokenParams);

            if (! $response->successful()) {
                Log::error('Erro ao trocar código por token no gov.br', ['status' => $response->status(), 'body' => $response->body()]);
                return redirect()->route('login')->with('error', 'Não foi possível autenticar junto ao servidor federal gov.br.');
            }

            $tokenData = $response->json();
            $accessToken = $tokenData['access_token'] ?? null;
            $idToken = $tokenData['id_token'] ?? null;

            // Validação do nonce no id_token se fornecido
            if ($idToken && $savedNonce) {
                $parts = explode('.', $idToken);
                if (count($parts) >= 2) {
                    $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
                    if (isset($payload['nonce']) && $payload['nonce'] !== $savedNonce) {
                        return redirect()->route('login')->with('error', 'Falha na validação de integridade criptográfica (nonce inválido).');
                    }
                }
            }

            // Extrair dados do usuário via UserInfo
            $userinfoEndpoint = config("govbr.endpoints.{$env}.userinfo");
            $userinfoRes = Http::withToken($accessToken)->get($userinfoEndpoint);

            if (! $userinfoRes->successful()) {
                return redirect()->route('login')->with('error', 'Erro ao obter dados cadastrais do cidadão no gov.br.');
            }

            $userData = $userinfoRes->json();
            $cpf = preg_replace('/\D/', '', $userData['sub'] ?? ($userData['cpf'] ?? ''));
            $nome = $userData['name'] ?? 'Cidadão gov.br';

            if (empty($cpf)) {
                return redirect()->route('login')->with('error', 'CPF não fornecido pelo provedor gov.br.');
            }

            // 1. Identificação de servidor público: EXCLUSIVAMENTE por CPF cadastrado (nunca por e-mail isolado)
            $user = User::where('cpf', $cpf)->first();

            if ($user && ! $user->ativo) {
                return redirect()->route('login')->with('error', 'Seu usuário está desativado. Procure o setor de TI da Prefeitura.');
            }

            if ($user) {
                Auth::login($user);
                $request->session()->regenerate(); // Prevenção contra session fixation

                return redirect()->intended(route('dashboard'))
                    ->with('success', "Bem-vindo(a), {$user->name}! Autenticado com sucesso via gov.br.");
            }

            // 2. Se não for servidor cadastrado, vincula ou cadastra munícipe no Portal do Cidadão
            $cliente = Cliente::where('cpf', $cpf)->first();
            if (! $cliente) {
                $cliente = Cliente::create([
                    'tipo_pessoa' => 'F',
                    'nome' => $nome,
                    'cpf' => $cpf,
                ]);
            }

            $request->session()->regenerate();

            session([
                'cidadao_autenticado_govbr' => true,
                'cidadao_id' => $cliente->id,
                'cidadao_nome' => $cliente->nome,
                'cidadao_cpf' => $cliente->cpf,
            ]);

            return redirect()->route('portal.index')
                ->with('success', "Olá, {$cliente->nome}! Você está autenticado no Portal do Cidadão via gov.br.");

        } catch (\Throwable $e) {
            Log::error('Exceção ao autenticar com gov.br', ['exception' => $e->getMessage()]);
            return redirect()->route('login')->with('error', 'Ocorreu um erro inesperado durante a comunicação com o gov.br.');
        }
    }
}
