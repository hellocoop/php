<?php

namespace HelloCoop\Tests\Handler;

use PHPUnit\Framework\TestCase;
use HelloCoop\Handler\Login;

class LoginTest extends TestCase
{
    private Login $login;
    public function setUp(): void
    {
        $this->login = new Login();
    }

    public function testCanGenerateLoginUrl(): void
    {
        $this->assertTrue(
            filter_var($this->login->generateLoginUrl(), FILTER_VALIDATE_URL) !== false,
            "The URL is not valid."
        );
    }
}
