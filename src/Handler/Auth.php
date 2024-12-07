<?php

namespace HelloCoop\Handler;

use HelloCoop\HelloResponse\HelloResponseInterface;
use HelloCoop\HelloRequest\HelloRequestInterface;
use HelloCoop\Config\ConfigInterface;
use HelloCoop\Lib\OIDCManager;
use HelloCoop\Lib\Crypto;
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
        if ($this->authLib instanceof AuthLib) {
            return $this->authLib;
        }
        $crypto = new Crypto($this->config->getSecret());
        return $this->authLib = new AuthLib(
            $this->helloRequest,
            $this->helloResponse,
            $this->config,
            new OIDCManager(
                $this->helloRequest,
                $this->helloResponse,
                $this->config,
                $crypto
            ),
            $crypto
        );
    }

    public function handleAuth(): ?AuthType
    {
        return $this->getAuthLib()->getAuthfromCookies();
    }
    public function updateAuth(AuthUpdates $authUpdates): ?AuthType
    {
        return null;
    }
    public function clearAuth(): void
    {
        $this->getAuthLib()->clearAuthCookie();
    }
}
