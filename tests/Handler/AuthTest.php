<?php

namespace HelloCoop\Tests\Handler;

use PHPUnit\Framework\TestCase;
use HelloCoop\Handler\Auth;
use HelloCoop\Lib\Auth as AuthLib;

class AuthTest extends TestCase
{
    private Auth $auth;
    private $authLibMock;
    public function setUp(): void
    {
        $this->authLibMock = $this->createMock(AuthLib::class);
        $this->auth = new Auth($this->authLibMock);
    }

    public function testCanHandleAuth(): void
    {
    }
}
