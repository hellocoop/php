<?php

namespace HelloCoop\Lib;

use Exception;
use HelloCoop\Type\Auth as AuthType;
use HelloCoop\Config\ConfigInterface;
use HelloCoop\HelloRequest\HelloRequestInterface;
use HelloCoop\HelloResponse\HelloResponseInterface;

class Auth
{
    private Crypto $crypto;
    private ConfigInterface $config;
    private HelloRequestInterface $helloRequest;
    private HelloResponseInterface $helloResponse;
    private OIDCManager $oidcManager;

    public function __construct(
        Crypto $crypto,
        HelloRequestInterface $helloRequest,
        HelloResponseInterface $helloResponse,
        OIDCManager $oidcManager,
        ConfigInterface $config
    ) {
        $this->crypto = $crypto;
        $this->helloRequest = $helloRequest;
        $this->helloResponse = $helloResponse;
        $this->oidcManager = $oidcManager;
        $this->config = $config;
    }

    public function saveAuthCookie(AuthType $auth): bool
    {
        try {
            $encCookie = $this->crypto->encrypt($auth->toArray());
            if (!$encCookie) {
                return false;
            }
            $this->helloResponse->setCookie($this->config->getCookies()['authName'], $encCookie);
            return true;
        } catch (Exception $e) {
            //TODO: log error
        }

        return false;
    }

    public function getAuthfromCookies(): AuthType
    {
        $oidCookie = $this->helloRequest->getCookie($this->config->getCookies()['oidcName']);
        if ($oidCookie) {
            $this->oidcManager->clearOidcCookie();
        }

        $authCookie = $this->helloRequest->getCookie($this->config->getCookies()['authName']);

        if (!$authCookie) {
            return AuthType::fromArray(['isLoggedIn' => false]);
        }

        try {
            $auth = $this->crypto->decrypt($authCookie);
            if (is_array($auth)) {
                if ($auth['isLoggedIn'] && $this->config->getCookieToken()) {
                    $auth = array_merge($auth, ['cookieToken' => $this->config->getCookieToken()]);
                }
                return AuthType::fromArray($auth);
            }
        } catch (Exception $e) {
            $this->clearAuthCookie();
            //TODO: log error
        }
        return  AuthType::fromArray(['isLoggedIn' => false]);
    }

    public function clearAuthCookie(): void
    {
        $this->helloResponse->deleteCookie($this->config->getCookies()['authName']);
    }
}
