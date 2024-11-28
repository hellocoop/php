<?php

namespace HelloCoop\Lib;

use Exception;
use HelloCoop\Type\Auth as AuthType;
use HelloCoop\Type\AuthCookie;
use HelloCoop\Cookie\CookieManagerInterface;

class Auth{
    private string $oidcName;
    private string $authName;
    private Crypto $crypto;

    private CookieManagerInterface $cookieManager;

    private ?string $cookieToken = null;

    public function __construct(
        string $oidcName, 
        string $authName, 
        Crypto $crypto, 
        CookieManagerInterface $cookieManager, 
        ?string $cookieToken = null
    )
    {
        $this->oidcName = $oidcName;
        $this->authName = $authName;
        $this->crypto = $crypto;
        $this->cookieManager = $cookieManager;

        $this->cookieToken = $cookieToken;
    }

    public function saveAuthCookie(AuthType $auth):bool {
        try {
            $encCookie = $this->crypto->encrypt($auth);
            if (!$encCookie) {
                return false;
            }
            $this->cookieManager->set($this->authName, $encCookie);

        }
        catch(Exception $e) {

        }

        return false;
    }

    public function getAuthfromCookies(): ?AuthType 
    {
        $cookieHeader = isset($_SERVER['HTTP_COOKIE']) ? $_SERVER['HTTP_COOKIE'] : '';
        $cookies = $this->parseCookies($cookieHeader);
        if ($cookies[$this->oidcName]) {
            //TODO: delete open id cookie.
        }

        $authCookie = $cookies[$this->authName];
        if (!$authCookie) {
            return null;
        }

        try{
            $auth = $this->crypto->decrypt($authCookie);
            if ($auth) {
                if ($auth['isLoggedIn'] && $this->cookieToken) {
                    return new AuthType(true, AuthCookie::fromArray($authCookie));
                }
                
            }
        }
        catch(Exception $e) {
            $this->cookieManager->delete($this->authName);
            //TODO: log error
        }
        return null;
    }

    public function clearAuthCookie():bool {
        return false;
    }

    private function parseCookies(string $cookieHeader): array {
        $cookies = [];
        // Split the cookie header into individual cookies
        $cookieArray = explode(';', $cookieHeader);
        foreach ($cookieArray as $cookie) {
            $cookie = trim($cookie); // Remove extra spaces
            if (strpos($cookie, '=') !== false) {
                list($name, $value) = explode('=', $cookie, 2);
                $cookies[urldecode($name)] = urldecode($value);
            }
        }
        return $cookies;
    }
}