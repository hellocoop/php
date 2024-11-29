<?php

namespace HelloCoop\Tests\Handler;

use HelloCoop\Config\HelloConfig;
use PHPUnit\Framework\TestCase;
use HelloCoop\Handler\Invite;

class InviteTest extends TestCase
{
    private Invite $invite;

    private $configMock;
    public function setUp(): void
    {
        $this->configMock = $this->createMock(HelloConfig::class);
        $this->invite = new Invite($this->configMock);
    }

    public function testCanGenerateInviteUrl(): void
    {
        $this->assertTrue(
            filter_var($this->invite->generateInviteUrl(), FILTER_VALIDATE_URL) !== false,
            "The URL is not valid."
        );
    }
}
