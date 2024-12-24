<?php

namespace HelloCoop\Tests\Handler;

use PHPUnit\Framework\TestCase;
use HelloCoop\Handler\Auth;
use HelloCoop\Tests\Traits\ServiceMocksTrait;

class AuthTest extends TestCase
{
    use ServiceMocksTrait;

    private Auth $auth;

    public function setUp(): void
    {
        parent::setUp();
        $this->setUpServiceMocks();

        $this->auth = new Auth(
            $this->helloRequestMock,
            $this->helloResponseMock,
            $this->configMock
        );
    }

    public function testCanHandleAuth(): void
    {
        $_COOKIE['authName'] = $this->crypto->encrypt([
            'isLoggedIn' => true,
            'authCookie' => [
                'sub' => 'user123',
                'iat' => time(),
                'name' => 'Dick Hardt',
                'picture' => 'https://pictures.hello.coop/r/7a160eed-46bf-48e2-a909-161745535895.png',
                'email' => 'dick.hardt@hello.coop'
            ]
        ]);

        $result = $this->auth->handleAuth();
        /** @var string $actual_name */
        $actual_name = isset($result->toArray()['authCookie']['name'])
            ? $result->toArray()['authCookie']['name']
            : '';
        /** @var string $actual_email */
        $actual_email = isset($result->toArray()['authCookie']['name'])
            ? $result->toArray()['authCookie']['email']
            : '';
        $this->assertTrue($result->toArray()['isLoggedIn']);
        $this->assertEquals('Dick Hardt', $actual_name);
        $this->assertEquals('dick.hardt@hello.coop', $actual_email);
    }
}
