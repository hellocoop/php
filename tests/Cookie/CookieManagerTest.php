<?php

namespace HelloCoop\Tests\Cookie;

use HelloCoop\Cookie\CookieManager;
use HelloCoop\Cookie\CookieManagerInterface;
use PHPUnit\Framework\TestCase;

class CookieManagerTest extends TestCase
{
    private CookieManagerInterface $cookieManager;

    protected function setUp(): void
    {
        $this->cookieManager = new CookieManager();
    }

    /**
     * @runInSeparateProcess
     */
    public function testSetCookie(): void
    {
        $this->markTestSkipped('Skipping due to a "headers already sent" issue with PHPUnit.');
        // Use output buffering to capture setcookie calls
        $this->expectOutputString('');
        ob_start();
        $this->cookieManager->set('test_cookie', 'test_value', time() + 3600);
        $myDebugVar = headers_list();
        ob_end_clean();
        fwrite(STDERR, print_r($myDebugVar, TRUE));
        $this->assertTrue(headers_sent());
    }

    public function testGetCookie(): void
    {
        $_COOKIE['test_cookie'] = 'test_value';

        $this->assertEquals('test_value', $this->cookieManager->get('test_cookie'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testDeleteCookie(): void
    {
        $this->markTestSkipped('Skipping due to a "headers already sent" issue with PHPUnit.');
        $this->cookieManager->delete('test_cookie');
        $cookieValue = $_COOKIE['test_cookie'] ?? false;
        $this->assertFalse($cookieValue, '');
    }
}
