<?php

namespace HelloCoop\Config;

interface ConfigInterface
{
    public function getProduction(): bool;
    public function getSameSiteStrict(): ?bool;
    public function getError(): ?array;
    public function getScope(): ?array;
    public function getProviderHint(): ?array;
    public function getRoutes(): array;
    public function getCookies(): array;
    public function getLoginSync(): ?callable;
    public function getLogoutSync(): ?callable;
    public function getCookieToken(): ?bool;
    public function getApiRoute(): string;
    public function getAuthApiRoute(): string;
    public function getLoginApiRoute(): string;
    public function getLogoutApiRoute(): string;
    public function getClientId(): ?string;
    public function getHost(): ?string;
    public function getRedirectURI(): ?string;
    public function getHelloDomain(): string;
    public function getHelloWallet(): string;
    public function getSecret(): ?string;
    public function getLogDebug(): ?bool;
}
