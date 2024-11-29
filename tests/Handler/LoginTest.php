<?php

namespace HelloCoop\Tests\Handler;

use HelloCoop\Config\HelloConfig;
use PHPUnit\Framework\TestCase;
use HelloCoop\Handler\Login;

class LoginTest extends TestCase
{
    private Login $login;
    private $configMock;
    public function setUp(): void
    {
        $this->configMock = $this->createMock(HelloConfig::class);
        $this->login = new Login($this->configMock);
    }

    public function testCanGenerateLoginUrl(): void
    {
        $this->assertTrue(
            filter_var($this->login->generateLoginUrl(), FILTER_VALIDATE_URL) !== false,
            "The URL is not valid."
        );
    }
}
