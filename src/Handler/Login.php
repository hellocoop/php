<?php

namespace HelloCoop\Handler;

use HelloCoop\Config\HelloConfig;

class Login{
    private HelloConfig $config;
    public function __construct(HelloConfig $config)
    {
        $this->config = $config;
    }

    public function generateLoginUrl(): string 
    {
       return ""; 
    }
}
