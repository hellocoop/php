<?php

namespace HelloCoop\Tests\Handler;

use PHPUnit\Framework\TestCase;
use HelloCoop\Handler\Logout;
use HelloCoop\Tests\Traits\ServiceMocksTrait;

class LogoutTest extends TestCase
{
    use ServiceMocksTrait;

    private Logout $logoutHandler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpServiceMocks();

        $this->logoutHandler = new Logout(
            $this->helloRequestMock,
            $this->helloResponseMock,
            $this->configMock
        );
    }

    public function testGenerateLogoutUrlWithTargetUri(): void
    {
        $_GET = [
            'target_uri' => 'https://example.com/target',
        ];

        $this->configMock
            ->method('getLoginSync')
            ->willReturn(null);

        $this->configMock
            ->method('getRoutes')
            ->willReturn(['loggedOut' => 'http://example.com/logout']);

        $result = $this->logoutHandler->generateLogoutUrl();

        $this->assertEquals('https://example.com/target', $result);
    }

    public function testGenerateLogoutUrlWithoutTargetUri(): void
    {
        $_GET = [
            'target_uri' => null,
        ];

        $this->configMock
            ->method('getLoginSync')
            ->willReturn(null);

        $this->configMock
            ->method('getRoutes')
            ->willReturn(['loggedOut' => 'http://example.com/logout']);

        $result = $this->logoutHandler->generateLogoutUrl();

        $this->assertEquals('http://example.com/logout', $result);
    }

    public function testGenerateLogoutUrlWithLoginSync(): void
    {
        $_GET = [
            'target_uri' => null,
        ];

        // Use an anonymous class to define the __invoke method
        $syncCallback = new class {
            public function __invoke(): void
            {
                // Simulate callable behavior
            }
        };

        $this->configMock
            ->method('getLogoutSync')
            ->willReturn($syncCallback);

        $this->configMock
            ->method('getRoutes')
            ->willReturn(['loggedOut' => 'http://example.com/logout']);

        $result = $this->logoutHandler->generateLogoutUrl();

        $this->assertEquals('http://example.com/logout', $result);
    }
}
