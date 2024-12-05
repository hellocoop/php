<?php

namespace HelloCoop\Handler;

use HelloCoop\Type\AuthUpdates;
use HelloCoop\Type\Auth as AuthType;
use HelloCoop\Lib\Auth as AuthLib;

class Auth
{
    private AuthLib $authLib;
    public function __construct(AuthLib $authLib)
    {
        $this->authLib = $authLib;
    }

    public function handleAuth(): ?AuthType
    {
        return $this->authLib->getAuthfromCookies();
    }
    public function updateAuth(AuthUpdates $authUpdates): ?AuthType
    {
        return null;
    }
    public function clearAuth(): void
    {
        $this->authLib->clearAuthCookie();
    }
}
