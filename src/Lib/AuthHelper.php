<?php

namespace HelloCoop\Lib;

use HelloCoop\Lib\PKCE;
use HelloCoop\Config\Constants;
use InvalidArgumentException;

class AuthHelper
{
    private PKCE $pkce;

    public function __construct(PKCE $pkce)
    {
        $this->pkce = $pkce;
    }
    public function createAuthRequest(array $config): array
    {
        // Validate required parameters
        $clientId = $config['client_id'] ?? null;
        $redirectUri = $config['redirect_uri'] ?? null;

        if (empty($clientId)) {
            throw new InvalidArgumentException('client_id is required in the authorization request.');
        }

        if (empty($redirectUri)) {
            throw new InvalidArgumentException('redirect_uri is required in the authorization request.');
        }

        $scopes = $config['scope'] ?? [];
        if ($scopes && !$this->areScopesValid($scopes)) {
            throw new InvalidArgumentException('One or more passed scopes are invalid.');
        }

        // Add 'openid' and ensure uniqueness
        $scopes = array_unique(array_merge($scopes ?? Constants::$DEFAULT_SCOPE, ['openid']));
        $nonce = $config['nonce'] ?? $this->generateUuid();

        // Prepare parameters
        $params = [
            'client_id'     => $clientId,
            'redirect_uri'  => $redirectUri,
            'scope'         => implode(' ', $scopes),
            'response_type' => $config['response_type'] ?? Constants::$DEFAULT_RESPONSE_TYPE,
            'response_mode' => $config['response_mode'] ?? Constants::$DEFAULT_RESPONSE_MODE,
            'nonce'         => $nonce,
        ];

        if ($prompt = $config['prompt'] ?? null) {
            $params['prompt'] = $prompt;
        }

        if ($params['response_type'] === 'code') {
            $pkceMaterial = $this->pkce->generate();
            $params['code_challenge'] = $pkceMaterial['code_challenge'];
            $params['code_challenge_method'] = 'S256';
        }

        if ($providerHint = $config['provider_hint'] ?? null) {
            $params['provider_hint'] = implode(' ', (array)$providerHint);
        }

        if ($loginHint = $config['login_hint'] ?? null) {
            $params['login_hint'] = $loginHint;
        }

        if ($domainHint = $config['domain_hint'] ?? null) {
            $params['domain_hint'] = $domainHint;
        }

        $wallet = $config['wallet'] ?? Constants::$PRODUCTION_WALLET;
        $url = $wallet . Constants::$DEFAULT_PATH . '?' . http_build_query($params);

        return [
            'url'           => $url,
            'nonce'         => $nonce,
            'code_verifier' => $pkceMaterial['code_verifier'] ?? '',
        ];
    }

    private function isValidScope(string $scope): bool
    {
        return in_array($scope, Constants::getValidScopes(), true);
    }

    private function areScopesValid(array $scopes): bool
    {
        foreach ($scopes as $scope) {
            if (!$this->isValidScope($scope)) {
                return false;
            }
        }
        return true;
    }

    private static function generateUuid(): string
    {
        return bin2hex(random_bytes(16));
    }
}
