<?php

namespace HelloCoop\Lib;

use Exception;
use HelloCoop\Type\Auth as AuthType;
use HelloCoop\HelloRequest\HelloRequestInterface;
use HelloCoop\HelloResponse\HelloResponseInterface;

class Auth
{
    private string $oidcName;
    private string $authName;
    private Crypto $crypto;
    private HelloRequestInterface $helloRequest;
    private HelloResponseInterface $helloResponse;
    private OIDCManager $oidcManager;

    private ?string $cookieToken = null;

    public function __construct(
        string $oidcName,
        string $authName,
        Crypto $crypto,
        HelloRequestInterface $helloRequest,
        HelloResponseInterface $helloResponse,
        OIDCManager $oidcManager,
        ?string $cookieToken = null
    ) {
        $this->oidcName = $oidcName;
        $this->authName = $authName;
        $this->crypto = $crypto;
        $this->helloRequest = $helloRequest;
        $this->helloResponse = $helloResponse;
        $this->oidcManager = $oidcManager;

        $this->cookieToken = $cookieToken;
    }

    public function saveAuthCookie(AuthType $auth): bool
    {
        try {
            $encCookie = $this->crypto->encrypt($auth->toArray());
            if (!$encCookie) {
                return false;
            }
            $this->helloResponse->setCookie($this->authName, $encCookie);
            return true;
        } catch (Exception $e) {
            //TODO: log error
        }

        return false;
    }

    public function getAuthfromCookies(): AuthType
    {
        $oidCookie = $this->helloRequest->getCookie($this->oidcName);
        if ($oidCookie) {
            $this->oidcManager->clearOidcCookie();
        }

        $authCookie = $this->helloRequest->getCookie($this->authName);

        if (!$authCookie) {
            return AuthType::fromArray(['isLoggedIn' => false]);
        }

        try {
            $auth = $this->crypto->decrypt($authCookie);
            if (is_array($auth)) {
                if ($auth['isLoggedIn'] && $this->cookieToken) {
                    $auth = array_merge($auth, ['cookieToken' => $this->cookieToken]);
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
        $this->helloResponse->deleteCookie($this->authName);
    }
}
