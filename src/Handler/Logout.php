<?php

namespace HelloCoop\Handler;

class Logout{
    private $logoutSync = null;
    public function __construct(
        string $targetUri,
        ?callable $logoutSync
    )
    {
        $this->logoutSync = $logoutSync;
    }
    public function generateLogoutUrl(): string 
    {
        //clearAuthCookie();
        if ($this->logoutSync) {
            // Call the logoutSync callback
            call_user_func($this->logoutSync);
        }
        return ""; 
    }
}