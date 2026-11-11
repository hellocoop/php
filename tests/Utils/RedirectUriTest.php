<?php

declare(strict_types=1);

namespace HelloCoop\Tests\Utils;

use HelloCoop\Utils\RedirectUri;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RedirectUriTest extends TestCase
{
    /** @return array<string, array{string}> */
    public static function acceptedProvider(): array
    {
        return [
            'absolute path' => ['/dashboard'],
            'path with query' => ['/dashboard?tab=1&x=y'],
            'https url' => ['https://example.com/target'],
            'http url with port' => ['http://localhost:8080/callback'],
            'uppercase scheme' => ['HTTPS://example.com/'],
        ];
    }

    #[DataProvider('acceptedProvider')]
    public function testAcceptedValuesAreReturnedUnchanged(string $uri): void
    {
        $this->assertSame($uri, RedirectUri::sanitize($uri, '/fallback'));
    }

    /** @return array<string, array{mixed}> */
    public static function rejectedProvider(): array
    {
        return [
            'null' => [null],
            'empty string' => [''],
            'array' => [['/dashboard']],
            'javascript scheme' => ['javascript:alert(1)'],
            'data scheme' => ['data:text/html,hi'],
            'scheme-relative' => ['//evil.example/'],
            'backslash scheme-relative' => ['/\\evil.example/'],
            'relative path' => ['dashboard'],
            'header injection' => ["/x\r\nSet-Cookie: a=b"],
            'whitespace' => ['/x y'],
            'missing host' => ['https:///path'],
        ];
    }

    #[DataProvider('rejectedProvider')]
    public function testRejectedValuesFallBack(mixed $uri): void
    {
        $this->assertSame('/fallback', RedirectUri::sanitize($uri, '/fallback'));
    }
}
