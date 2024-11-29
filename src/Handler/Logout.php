<?php

namespace HelloCoop\Handler;

use HelloCoop\Config\HelloConfig;

class Logout{

    private HelloConfig $config;
    public function __construct(HelloConfig $config)
    {
        $this->config = $config;
    }
    public function generateLogoutUrl(): string 
    {
        //clearAuthCookie();
        if ($this->config->getLoginSync()) {
            // Call the logoutSync callback
            call_user_func($this->config->getLoginSync());
        }
        return ""; 
    }
}