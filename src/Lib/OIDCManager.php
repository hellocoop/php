<?php

namespace HelloCoop\Lib;

use HelloCoop\Type\OIDC;
use HelloCoop\HelloRequest\HelloRequestInterface;
use HelloCoop\HelloResponse\HelloResponseInterface;
use Exception;

class OIDCManager
{
    private HelloResponseInterface $helloResponse;
    private HelloRequestInterface $helloRequest;
    private Crypto $crypto;
    private string $oidcName;
    private array $config;
    private string $apiRoute;

    public function __construct(
        HelloRequestInterface $helloRequest,
        HelloResponseInterface $helloResponse,
        Crypto $crypto,
        string $oidcName,
        array $config,
        string $path = '/'
    ) {
        $this->helloRequest = $helloRequest;
        $this->helloResponse = $helloResponse;
        $this->crypto = $crypto;
        $this->oidcName = $oidcName;
        $this->config = $config;
        $this->apiRoute = $path;
    }

    public function getOidc(): ?OIDC
    {
        $oidcCookie = $this->helloRequest->getCookie($this->oidcName);

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

    public function saveOidc(OIDC $oidc): void
    {
        try {
            $encCookie = $this->crypto->encrypt($oidc->toArray());

            $this->helloResponse->setCookie(
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

    public function clearOidcCookie(): void
    {
        $this->helloResponse->deleteCookie(
            $this->oidcName,
            $this->apiRoute,
            ''
        );
    }
}
