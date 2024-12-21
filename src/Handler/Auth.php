<?php

namespace HelloCoop\Handler;

use HelloCoop\HelloResponse\HelloResponseInterface;
use HelloCoop\HelloRequest\HelloRequestInterface;
use HelloCoop\Config\ConfigInterface;
use HelloCoop\Type\AuthUpdates;
use HelloCoop\Type\Auth as AuthType;
use HelloCoop\Lib\Auth as AuthLib;

class Auth
{
    private HelloResponseInterface $helloResponse;
    private HelloRequestInterface $helloRequest;
    private ConfigInterface $config;
    private ?AuthLib $authLib = null;

    public function __construct(
        HelloRequestInterface $helloRequest,
        HelloResponseInterface $helloResponse,
        ConfigInterface $config
    ) {
        $this->helloRequest = $helloRequest;
        $this->helloResponse = $helloResponse;
        $this->config = $config;
    }

    private function getAuthLib(): AuthLib
    {
        return $this->authLib ??= new AuthLib(
            $this->helloRequest,
            $this->helloResponse,
            $this->config,
        );
    }

    public function handleAuth(): ?AuthType
    {
        return $this->getAuthLib()->getAuthfromCookies();
    }

    public function updateAuth(AuthUpdates $authUpdates): ?AuthType
    {
        $auth = $this->getAuthLib()->getAuthfromCookies();
        if ($auth->isLoggedIn === false) {
            return $auth;
        }

        $updatedAuth = array_merge($auth->toArray(), $authUpdates->toArray());
        return AuthType::fromArray($updatedAuth);
    }

    public function clearAuth(): void
    {
        $this->getAuthLib()->clearAuthCookie();
    }
}
