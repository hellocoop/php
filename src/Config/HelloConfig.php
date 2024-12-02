<?php

namespace HelloCoop\Config;

class HelloConfig
{
    private ?string $clientId;
    private array $scope;
    private array $providerHint;
    private array $routes;
    private $loginSync;
    private $logoutSync;
    private ?string $redirectURI;
    private string $helloDomain;
    private string $helloWallet;

    // New fields
    private ?string $nonce;
    private ?string $responseType;
    private ?string $responseMode;
    private ?string $prompt;
    private ?string $loginHint;
    private ?string $domainHint;

    public function __construct(
        ?string $clientId = null,
        array $scope = ['openid', 'name', 'email', 'picture'],
        array $providerHint = ['github'],
        array $routes = [
            'loggedIn' => '/',
            'loggedOut' => '/',
            'error' => '/error',
        ],
        ?callable $loginSync = null,
        ?callable $logoutSync = null,
        $redirectURI = null,
        $helloWallet = '',
        $helloDomain = 'hello.coop',
        ?string $nonce = null,
        ?string $responseType = null,
        ?string $responseMode = null,
        ?string $prompt = null,
        ?string $loginHint = null,
        ?string $domainHint = null
    ) {
        $this->clientId = $clientId;
        $this->scope = $scope;
        $this->providerHint = $providerHint;
        $this->routes = $routes;
        $this->loginSync = $loginSync;
        $this->logoutSync = $logoutSync;
        $this->redirectURI = $redirectURI;
        $this->helloWallet = $helloWallet;
        $this->helloDomain = $helloDomain;
        $this->nonce = $nonce;
        $this->responseType = $responseType;
        $this->responseMode = $responseMode;
        $this->prompt = $prompt;
        $this->loginHint = $loginHint;
        $this->domainHint = $domainHint;
    }

    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    public function getScope(): array
    {
        return $this->scope;
    }

    public function getProviderHint(): array
    {
        return $this->providerHint;
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function getLoginSync(): ?callable
    {
        return $this->loginSync;
    }

    public function getLogoutSync(): ?callable
    {
        return $this->logoutSync;
    }

    public function getRedirectURI(): ?string
    {
        return $this->redirectURI;
    }

    public function getHelloDomain(): string
    {
        return $this->helloDomain;
    }

    public function getHelloWallet(): string
    {
        return $this->helloWallet;
    }

    // Getter methods for new fields
    public function getNonce(): ?string
    {
        return $this->nonce;
    }

    public function getResponseType(): ?string
    {
        return $this->responseType;
    }

    public function getResponseMode(): ?string
    {
        return $this->responseMode;
    }

    public function getPrompt(): ?string
    {
        return $this->prompt;
    }

    public function getLoginHint(): ?string
    {
        return $this->loginHint;
    }

    public function getDomainHint(): ?string
    {
        return $this->domainHint;
    }
}
