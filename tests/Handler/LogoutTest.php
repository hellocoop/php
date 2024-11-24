<?php

namespace HelloCoop\Tests\Handler;

use PHPUnit\Framework\TestCase;
use HelloCoop\Handler\Logout;

class LogoutTest extends TestCase
{
    private Logout $logout;
    public function setUp(): void
    {
        $this->logout = new Logout();
    }

    public function testCanCreateLogoutRedirectURL(): void
    {
        $this->assertTrue(
            filter_var($this->logout->createLogoutRedirectURL(), FILTER_VALIDATE_URL) !== false,
            "The URL is not valid."
        );
    }
}
