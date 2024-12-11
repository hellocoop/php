<?php

declare(strict_types=1);

namespace HelloCoop\Tests\Config;

use HelloCoop\Config\HelloConfig;
use PHPUnit\Framework\TestCase;

class HelloConfigTest extends TestCase
{
    private HelloConfig $config;

    protected function setUp(): void
    {
        $this->config = new HelloConfig(
            '/api',
            '/auth',
            '/login',
            '/logout',
            true,
            'client123',
            'https://example.com/callback',
            'example.com',
            'secret123',
            [
                'authName' => 'custom_auth',
                'oidcName' => 'custom_oidc',
            ],
            false,
            'custom.hello.coop',
            'wallet123',
            ['openid', 'email'],
            ['google'],
            [
                'loggedIn' => '/home',
                'loggedOut' => '/logout',
                'error' => '/error-page',
            ],
            function () {
                return true;
            },
            function () {
                return false;
            },
            true,
            true,
            ['code' => 404, 'message' => 'Not Found']
        );
    }

    public function testHelloConfigInitialization(): void
    {
        $this->assertSame('/api', $this->config->getApiRoute());
        $this->assertSame('/auth', $this->config->getAuthApiRoute());
        $this->assertSame('/login', $this->config->getLoginApiRoute());
        $this->assertSame('/logout', $this->config->getLogoutApiRoute());
        $this->assertTrue($this->config->getSameSiteStrict());
        $this->assertSame('client123', $this->config->getClientId());
        $this->assertSame('https://example.com/callback', $this->config->getRedirectURI());
        $this->assertSame('example.com', $this->config->getHost());
        $this->assertSame('secret123', $this->config->getSecret());
        $this->assertSame(
            ['authName' => 'custom_auth', 'oidcName' => 'custom_oidc'],
            $this->config->getCookies()
        );
        $this->assertFalse($this->config->getProduction());
        $this->assertSame('custom.hello.coop', $this->config->getHelloDomain());
        $this->assertSame('wallet123', $this->config->getHelloWallet());
        $this->assertSame(['openid', 'email'], $this->config->getScope());
        $this->assertSame(['google'], $this->config->getProviderHint());
        $this->assertSame(
            ['loggedIn' => '/home', 'loggedOut' => '/logout', 'error' => '/error-page'],
            $this->config->getRoutes()
        );
        $this->assertNotNull($this->config->getLoginSync());
        $this->assertNotNull($this->config->getLogoutSync());
        $this->assertTrue($this->config->getCookieToken());
        $this->assertTrue($this->config->getLogDebug());
        $this->assertSame(['code' => 404, 'message' => 'Not Found'], $this->config->getError());
    }
}
