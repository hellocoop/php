<?php

namespace HelloCoop\Handler;

use HelloCoop\Type\AuthUpdates;
use HelloCoop\Type\Auth as AuthType;
use HelloCoop\Lib\Auth as AuthLib;
use HelloCoop\HelloResponse\HelloResponseInterface;

class Auth
{
    private AuthLib $authLib;
    public function __construct(AuthLib $authLib)
    {
        $this->authLib = $authLib;
    }

    public function handleAuth(HelloResponseInterface $response): ?AuthType
    {
        $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, proxy-revalidate');
        $response->setHeader('Pragma', 'no-cache');
        $response->setHeader('Expires', '0');
        return $this->authLib->getAuthfromCookies();
    }
    public function updateAuth(AuthUpdates $authUpdates): ?AuthType
    {
        return null;
    }
    public function clearAuth(): bool
    {
        return false;
    }
}
