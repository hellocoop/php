<?php

namespace HelloCoop;

use HelloCoop\Config\HelloConfig;

class HelloClient
{
    private HelloConfig $config;
    public function __construct(HelloConfig $config)
    {
        $this->config = $config;
    }
    public function getAuth(): array
    {
        return ['isLoggedIn' => false];
    }
    public function login()
    {
    }
    public function logout()
    {
    }
    public function invite()
    {
    }
}
