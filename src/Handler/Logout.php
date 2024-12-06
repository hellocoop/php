<?php

namespace HelloCoop\Handler;

use HelloCoop\Config\ConfigInterface;
use HelloCoop\HelloRequest\HelloRequestInterface;
use HelloCoop\Lib\Auth;

class Logout
{
    private ConfigInterface $config;
    private HelloRequestInterface $helloRequest;
    private Auth $auth;
    public function __construct(
        ConfigInterface $config,
        HelloRequestInterface $helloRequest,
        Auth $auth
    ) {
        $this->config = $config;
        $this->helloRequest = $helloRequest;
        $this->auth = $auth;
    }
    public function generateLogoutUrl(): string
    {
        $targetUri = $this->helloRequest->fetch('target_uri');
        $this->auth->clearAuthCookie();
        if ($this->config->getLoginSync()) {
            // Call the logoutSync callback
            call_user_func($this->config->getLoginSync());
        }
        return $targetUri ?? $this->config->getRoutes()['loggedOut'];
    }
}
