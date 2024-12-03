<?php

namespace HelloCoop\Handler;

use HelloCoop\Config\HelloConfig;
use HelloCoop\RequestParamFetcher\ParamFetcherInterface;
use HelloCoop\Lib\Auth;

class Logout
{
    private HelloConfig $config;
    private ParamFetcherInterface $paramFetcher;
    private Auth $auth;
    public function __construct(
        HelloConfig $config,
        ParamFetcherInterface $paramFetcher,
        Auth $auth
    ) {
        $this->config = $config;
        $this->paramFetcher = $paramFetcher;
        $this->auth = $auth;
    }
    public function generateLogoutUrl(): string
    {
        $targetUri = $this->paramFetcher->fetch('target_uri');
        $this->auth->clearAuthCookie();
        if ($this->config->getLoginSync()) {
            // Call the logoutSync callback
            call_user_func($this->config->getLoginSync());
        }
        return $targetUri ?? $this->config->getRoutes()['loggedOut'];
    }
}
