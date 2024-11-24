<?php

namespace HelloCoop\Tests\Handler;

use PHPUnit\Framework\TestCase;
use HelloCoop\Handler\Invite;

class InviteTest extends TestCase
{
    private Invite $invite;
    public function setUp(): void
    {
        $this->invite = new Invite();
    }

    public function testCanGenerateInviteUrl(): void
    {
        $this->assertTrue(
            filter_var($this->invite->generateInviteUrl(), FILTER_VALIDATE_URL) !== false,
            "The URL is not valid."
        );
    }
}
