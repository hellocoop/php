<?php

namespace HelloCoop\Tests;

use HelloCoop\Cookie\CookieManager;
use PHPUnit\Framework\TestCase;

class CookieManagerTest extends TestCase
{
    private CookieManager $cookieManager;

    protected function setUp(): void
    {
        $this->cookieManager = new CookieManager();
    }

    public function testSetCookie(): void
    {
        // Use output buffering to capture setcookie calls
        $this->expectOutputString('');

        $this->cookieManager->set('test_cookie', 'test_value', time() + 3600);

        $this->assertTrue(headers_sent());
    }

    public function testGetCookie(): void
    {
        $_COOKIE['test_cookie'] = 'test_value';

        $this->assertEquals('test_value', $this->cookieManager->get('test_cookie'));
    }

    public function testDeleteCookie(): void
    {
        $this->cookieManager->delete('test_cookie');

        $cookieValue = $_COOKIE['test_cookie'] ?? false;
        $this->assertFalse($cookieValue, '');
    }
}
