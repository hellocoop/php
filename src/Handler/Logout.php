<?php

namespace HelloCoop\Handler;

use HelloCoop\HelloResponse\HelloResponseInterface;
use HelloCoop\HelloRequest\HelloRequestInterface;
use HelloCoop\Config\ConfigInterface;
use HelloCoop\Lib\Auth as AuthLib;
use HelloCoop\Lib\OIDCManager;
use HelloCoop\Lib\Crypto;

class Logout
{
    private HelloResponseInterface $helloResponse;
    private HelloRequestInterface $helloRequest;
    private ConfigInterface $config;
    private AuthLib $authLib;
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

    public function generateLogoutUrl(): string
    {
        $targetUri = $this->helloRequest->fetch('target_uri');
        $this->getAuthLib()->clearAuthCookie();
        if ($this->config->getLoginSync()) {
            // Call the logoutSync callback
            call_user_func($this->config->getLoginSync());
        }
        return $targetUri ?? $this->config->getRoutes()['loggedOut'];
    }
}
