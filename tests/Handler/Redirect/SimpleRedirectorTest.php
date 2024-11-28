<?php

namespace HelloCoop\Tests\Handler\Redirect;

use PHPUnit\Framework\TestCase;
use HelloCoop\Handler\Redirect\SimpleRedirector;

class SimpleRedirectorTest extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testRedirect(): void
    {
        $redirector = new SimpleRedirector();
        $url = 'https://example.com';

        // Capture output buffer
        $this->expectOutputString('');

        // Check that headers are sent correctly
        $this->setOutputCallback(function () use ($url) {
            $headers = xdebug_get_headers(); // Works in PHPUnit to fetch headers
            $this->assertContains("Location: $url", $headers);
        });

        // Simulate exit using PHPUnit's method
        $this->expectException(\PHPUnit\Framework\Error\Error::class);
        $redirector->redirect($url);
    }
}
