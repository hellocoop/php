<?php

namespace HelloCoop\Config;

class HelloConfigBuilder
{
    private string $apiRoute;
    private string $authApiRoute;
    private string $loginApiRoute;
    private string $logoutApiRoute;
    private bool $sameSiteStrict;
    private ?string $clientId = null;
    private ?string $redirectURI = null;
    private string $host = '';
    private ?string $secret = null;
    private array $cookies = [
        'authName' => 'hellocoop_auth',
        'oidcName' => 'hellocoop_oidc',
    ];
    private bool $production = true;
    private string $helloDomain = 'hello.coop';
    private ?string $helloWallet = null;
    private array $scope = ['openid', 'name', 'email', 'picture'];
    private array $providerHint = ['github'];
    private array $routes = [
        'loggedIn' => '/',
        'loggedOut' => '/',
        'error' => '/error',
    ];
    private $loginSync = null;
    private $logoutSync = null;
    private ?bool $cookieToken = null;
    private ?bool $logDebug = null;
    private ?array $error = null;

    public function setApiRoute(string $apiRoute): self
    {
        $this->apiRoute = $apiRoute;
        return $this;
    }

    public function setAuthApiRoute(string $authApiRoute): self
    {
        $this->authApiRoute = $authApiRoute;
        return $this;
    }

    public function setLoginApiRoute(string $loginApiRoute): self
    {
        $this->loginApiRoute = $loginApiRoute;
        return $this;
    }

    public function setLogoutApiRoute(string $logoutApiRoute): self
    {
        $this->logoutApiRoute = $logoutApiRoute;
        return $this;
    }

    public function setSameSiteStrict(bool $sameSiteStrict): self
    {
        $this->sameSiteStrict = $sameSiteStrict;
        return $this;
    }

    public function setClientId(?string $clientId): self
    {
        $this->clientId = $clientId;
        return $this;
    }

    public function setRedirectURI(?string $redirectURI): self
    {
        $this->redirectURI = $redirectURI;
        return $this;
    }

    public function setHost(string $host): self
    {
        $this->host = $host;
        return $this;
    }

    public function setSecret(?string $secret): self
    {
        $this->secret = $secret;
        return $this;
    }

    public function setCookies(array $cookies): self
    {
        $this->cookies = $cookies;
        return $this;
    }

    public function setProduction(bool $production): self
    {
        $this->production = $production;
        return $this;
    }

    public function setHelloDomain(string $helloDomain): self
    {
        $this->helloDomain = $helloDomain;
        return $this;
    }

    public function setHelloWallet(?string $helloWallet): self
    {
        $this->helloWallet = $helloWallet;
        return $this;
    }

    public function setScope(array $scope): self
    {
        $this->scope = $scope;
        return $this;
    }

    public function setProviderHint(array $providerHint): self
    {
        $this->providerHint = $providerHint;
        return $this;
    }

    public function setRoutes(array $routes): self
    {
        $this->routes = $routes;
        return $this;
    }

    public function setLoginSync(?callable $loginSync): self
    {
        $this->loginSync = $loginSync;
        return $this;
    }

    public function setLogoutSync(?callable $logoutSync): self
    {
        $this->logoutSync = $logoutSync;
        return $this;
    }

    public function setCookieToken(?bool $cookieToken): self
    {
        $this->cookieToken = $cookieToken;
        return $this;
    }

    public function setLogDebug(?bool $logDebug): self
    {
        $this->logDebug = $logDebug;
        return $this;
    }

    public function setError(?array $error): self
    {
        $this->error = $error;
        return $this;
    }

    public function build(): HelloConfig
    {
        return new HelloConfig(
            $this->apiRoute,
            $this->authApiRoute,
            $this->loginApiRoute,
            $this->logoutApiRoute,
            $this->sameSiteStrict,
            $this->clientId,
            $this->redirectURI,
            $this->host,
            $this->secret,
            $this->cookies,
            $this->production,
            $this->helloDomain,
            $this->helloWallet,
            $this->scope,
            $this->providerHint,
            $this->routes,
            $this->loginSync,
            $this->logoutSync,
            $this->cookieToken,
            $this->logDebug,
            $this->error
        );
    }
}
