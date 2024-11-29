<?php

namespace HelloCoop\Config;

class HelloConfig
{
    private string $clientId;
    private array $scope;
    private array $providerHint;
    private array $routes;
    private $loginSync;
    private $logoutSync;

    public function __construct(
        string $clientId,
        array $scope = ['openid', 'name', 'email', 'picture'],
        array $providerHint = ['github'],
        array $routes = [
            'loggedIn' => '/',
            'loggedOut' => '/',
            'error' => '/error',
        ],
        ?callable $loginSync = null,
        ?callable $logoutSync = null
    ) {
        $this->clientId = $clientId;
        $this->scope = $scope;
        $this->providerHint = $providerHint;
        $this->routes = $routes;
        $this->loginSync = $loginSync;
        $this->logoutSync = $logoutSync;
    }

    public function getClientId(): string
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
}
