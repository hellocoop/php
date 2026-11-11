<?php

declare(strict_types=1);

namespace HelloCoop\Tests\Renderers;

use HelloCoop\Renderers\DefaultPageRenderer;
use PHPUnit\Framework\TestCase;

class DefaultPageRendererTest extends TestCase
{
    public function testWildcardConsoleEscapesRequestValues(): void
    {
        $uri = 'https://console.hello.coop/?x=1"); alert(1); ("';
        $html = (new DefaultPageRenderer())->renderWildcardConsole(
            $uri,
            '/done"><script>alert(2)</script>',
            '<b>My App</b>',
            'https://example.com/cb?a=1&b=2'
        );

        // The quote that would end the JS string literal is encoded, so no breakout
        $this->assertStringNotContainsString('"); alert(1)', $html);
        $this->assertStringNotContainsString('<script>alert(2)</script>', $html);
        $this->assertStringNotContainsString('<b>My App</b>', $html);
        $this->assertStringContainsString('&lt;b&gt;My App&lt;/b&gt;', $html);
        $this->assertStringContainsString('https://example.com/cb?a=1&amp;b=2', $html);
        $jsonFlags = JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
        $this->assertStringContainsString('window.open(' . json_encode($uri, $jsonFlags) . ', "_blank")', $html);
    }
}
