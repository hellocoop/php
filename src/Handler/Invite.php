<?php

namespace HelloCoop\Handler;

use HelloCoop\Config\HelloConfig;
class Invite{
    private HelloConfig $config;
    public function __construct(HelloConfig $config)
    {
        $this->config = $config;
    }

    public function generateInviteUrl(): string 
    {
       return ""; 
    }
}
