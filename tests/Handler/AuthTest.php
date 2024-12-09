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
        $this->assertTrue($result->toArray()['isLoggedIn']);
        $this->assertEquals($result->toArray()['authCookie']['name'], 'Dick Hardt');
        $this->assertEquals($result->toArray()['authCookie']['email'], 'dick.hardt@hello.coop');
    }
}
