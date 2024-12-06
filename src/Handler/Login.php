<?php

namespace HelloCoop\Handler;

use HelloCoop\Config\ConfigInterface;
use HelloCoop\HelloRequest\HelloRequestInterface;
use HelloCoop\Lib\Auth;
use HelloCoop\Lib\AuthHelper;
use RuntimeException;
use HelloCoop\Type\OIDC;
use HelloCoop\Lib\OIDCManager;

class Login
{
    private ConfigInterface $config;
    private Auth $auth;
    private HelloRequestInterface $helloRequest;
    private OIDCManager $oidcManager;
    private AuthHelper $authHelper;
    private array $redirectURIs;

    public function __construct(
        ConfigInterface $config,
        Auth $auth,
        HelloRequestInterface $helloRequest,
        OIDCManager $oidcManager,
        AuthHelper $authHelper,
        array $redirectURIs = []
    ) {
        $this->config = $config;
        $this->auth = $auth;
        $this->helloRequest = $helloRequest;
        $this->oidcManager = $oidcManager;
        $this->authHelper = $authHelper;

        $this->redirectURIs = $redirectURIs;
    }

    public function generateLoginUrl(): ?string
    {
        $params = $this->helloRequest->fetchMultiple([
            'provider_hint',
            'scope',
            'target_uri',
            'redirect_uri',
            'nonce',
            'prompt',
            'login_hint',
            'domain_hint'
        ]);

        if (empty($this->config->getClientId())) {
            throw new RuntimeException('Missing HELLO_CLIENT_ID configuration');
        }

        $redirectURI = $this->config->getRedirectURI();
        $host = $this->helloRequest->fetchHeader('Host');

        if (empty($redirectURI)) {
            if (isset($this->redirectURIs[$host])) {
                $redirectURI = $this->redirectURIs[$host];
            } elseif (!empty($params['redirect_uri'])) {
                $this->redirectURIs[$host] = $redirectURI = $params['redirect_uri'];
                error_log("Hello: RedirectURI for $host => $redirectURI");
            } else {
                error_log('Hello: Discovering API RedirectURI route ...');
                //TODO: Implement bounce logic if needed
                throw new RuntimeException('RedirectURI not found');
            }
        }

        $providerHintString = $params['provider_hint'];
        $providerHint = $providerHintString ? array_map('trim', explode(' ', $providerHintString)) : null;

        $scopeString = $params['scope'];
        $scope = $scopeString ? array_map('trim', explode(' ', $scopeString)) : null;

        $request = [
            'redirect_uri' => $redirectURI,
            'client_id' => $this->config->getClientId(),
            'wallet' => $this->config->getHelloWallet(),
            'scope' => $scope,
            'provider_hint' => $providerHint,
            'login_hint' => $params['login_hint'] ?? null,
            'domain_hint' => $params['domain_hint'] ?? null,
            'prompt' => $params['prompt'] ?? null,
        ];

        if (!empty($params['nonce'])) {
            $request['nonce'] = $params['nonce'];
        }

        $authResponse = $this->authHelper->createAuthRequest($request);

        $this->oidcManager->saveOidc(OIDC::fromArray([
            'nonce' => $authResponse['nonce'],
            'code_verifier' => $authResponse['code_verifier'],
            'redirect_uri' => $redirectURI,
            'target_uri' => $params['target_uri'],
        ]));

        return $authResponse['url'];
    }
}
