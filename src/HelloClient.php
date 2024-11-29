<?php

namespace HelloCoop;

use HelloCoop\Config\HelloConfig;

class HelloClient{
    private HelloConfig $config;
    public function __construct(HelloConfig $config)
    {
        $this->config = $config;
    }
    public function getAuth() {}
    public function login() {}
    public function logout() {}
}