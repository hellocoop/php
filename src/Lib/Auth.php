<?php

namespace HelloCoop\Lib;

use Exception;
use HelloCoop\Type\Auth as AuthType;
use HelloCoop\Cookie\CookieManagerInterface;

class Auth{
    private string $oidcName;
    private string $authName;
    private Crypto $crypto;

    private CookieManagerInterface $cookieManager;

    private OIDCManager $oidcManager;

    private ?string $cookieToken = null;

    public function __construct(
        string $oidcName, 
        string $authName, 
        Crypto $crypto, 
        CookieManagerInterface $cookieManager,
        OIDCManager $oidcManager,
        ?string $cookieToken = null
    )
    {
        $this->oidcName = $oidcName;
        $this->authName = $authName;
        $this->crypto = $crypto;
        $this->cookieManager = $cookieManager;
        $this->oidcManager = $oidcManager;

        $this->cookieToken = $cookieToken;
    }

    public function saveAuthCookie(AuthType $auth):bool {
        try {
            $encCookie = $this->crypto->encrypt($auth->toArray());
            if (!$encCookie) {
                return false;
            }
            $this->cookieManager->set($this->authName, $encCookie);
            return true;

        }
        catch(Exception $e) {
            //TODO: log error
        }

        return false;
    }

    public function getAuthfromCookies(): ?AuthType 
    {
        $oidCookie = $this->cookieManager->get($this->oidcName);
        if ($oidCookie) {
            $this->oidcManager->clearOidcCookie();
        }
        
        $authCookie = $this->cookieManager->get($this->authName);
        if (!$authCookie) {
            return null;
        }

        try{
            $auth = $this->crypto->decrypt($authCookie);

            if (is_array($auth)) {
                if ($auth['isLoggedIn'] && $this->cookieToken) {
                    $auth = array_merge($auth, ['cookieToken' => $this->cookieToken]);
                }
                return AuthType::fromArray($auth);
                
            }
        }
        catch(Exception $e) {
            $this->clearAuthCookie();
            //TODO: log error
        }
        return null;
    }

    public function clearAuthCookie():void {
        $this->cookieManager->delete($this->authName);
    }
}