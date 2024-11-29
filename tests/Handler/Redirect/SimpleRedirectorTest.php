<?php

namespace HelloCoop\Tests\Handler\Redirect;

use PHPUnit\Framework\TestCase;
use HelloCoop\Handler\Redirect\SimpleRedirector;
use PHPUnit\Framework\Error\Error;

define('TESTING', true);

class SimpleRedirectorTest extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testRedirect(): void
    {
        $this->markTestSkipped('Skipping due to a "headers already sent" issue with PHPUnit.');
        $redirector = new SimpleRedirector();
        $url = 'https://example.com';

        $this->expectOutputString('');
        $this->setOutputCallback(function () use ($url) {
            $headers = headers_list();
            fwrite(STDERR, print_r($headers, TRUE));
            $this->assertContains("Location: $url", $headers);
        });

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Exit called');

        $redirector->redirect($url);
    }
}
