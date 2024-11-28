<?php

namespace HelloCoop\Lib;

use HelloCoop\Type\OIDC;
use HelloCoop\Cookie\CookieManagerInterface;
use Exception;

class OIDCManager {
    private CookieManagerInterface $cookieManager;
    private Crypto $crypto;
    private string $oidcName;
    private array $config;
    private string $apiRoute = '/';

    public function __construct(
        CookieManagerInterface $cookieManager,
        Crypto $crypto,
        string $oidcName,
        array $config
    ) {
        $this->cookieManager = $cookieManager;
        $this->crypto = $crypto;
        $this->oidcName = $oidcName;
        $this->config = $config;
    }

    public function getOidc(): ?OIDC {
        $oidcCookie = $this->cookieManager->get($this->oidcName);
        if (!$oidcCookie) {
            return null;
        }

        try {
            $oidcData = $this->crypto->decrypt($oidcCookie);
            if (is_array($oidcData)) {
                return OIDC::fromArray($oidcData);
            }
        } catch (Exception $e) {
            $this->clearOidcCookie();
            // TODO: Log error
        }

        return null;
    }

    public function saveOidc(string $path, OIDC $oidc): void {
        if ($this->apiRoute === '/') {
            $this->apiRoute = $path;
        }

        try {
            $encCookie = $this->crypto->encrypt($oidc->toArray());

            $this->cookieManager->set(
                $this->oidcName,
                $encCookie,
                time() + 5 * 60, // 5 minutes
                $this->apiRoute,
                '',
                $this->config['production'],
                true // HttpOnly
            );
        } catch (Exception $e) {
            // TODO: Log error
        }
    }

    public function clearOidcCookie(): void {
        $this->cookieManager->delete(
            $this->oidcName,
            $this->apiRoute,
            ''
        );
    }
}
