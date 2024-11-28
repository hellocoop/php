<?php

namespace HelloCoop\Tests\Handler;

use PHPUnit\Framework\TestCase;
use HelloCoop\Handler\Auth;

class AuthTest extends TestCase
{
    private Auth $auth;
    public function setUp(): void
    {
        $this->auth = new Auth();
    }

    public function testCanHandleAuth(): void
    {
    }
}
