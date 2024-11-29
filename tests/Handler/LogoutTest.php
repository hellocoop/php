<?php

namespace HelloCoop\Tests\Handler;

use HelloCoop\Config\HelloConfig;
use PHPUnit\Framework\TestCase;
use HelloCoop\Handler\Logout;

class LogoutTest extends TestCase
{
    private Logout $logout;
    private $configMock;
    public function setUp(): void
    {
        $this->configMock = $this->createMock(HelloConfig::class);
        $this->logout = new Logout($this->configMock);
    }

    public function testCanGenerateLogoutUrl(): void
    {
        $this->assertTrue(
            filter_var($this->logout->generateLogoutUrl(), FILTER_VALIDATE_URL) !== false,
            "The URL is not valid."
        );
    }
}
