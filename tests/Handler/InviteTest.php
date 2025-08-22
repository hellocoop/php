<?php

namespace HelloCoop\Tests\Handler;

use PHPUnit\Framework\TestCase;
use HelloCoop\Handler\Invite;
use HelloCoop\Tests\Traits\ServiceMocksTrait;

class InviteTest extends TestCase
{
    use ServiceMocksTrait;

    private Invite $invite;
    /**
     * @var array<string, mixed>
     */
    private array $originalGet;
    /**
     * @var array<string, mixed>
     */
    private array $originalCookie;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpServiceMocks();

        // Backup superglobals
        $this->originalGet = $_GET;
        $this->originalCookie = $_COOKIE;

        $this->invite = new Invite(
            $this->helloRequestMock,
            $this->helloResponseMock,
            $this->configMock
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Restore superglobals after the test
        $_GET = $this->originalGet;
        $_COOKIE = $this->originalCookie;
    }

    public function testCanGenerateInviteUrl(): void
    {

        $_GET = [
            'target_uri' => 'https://example.com',
            'app_name' => 'MyApp',
            'prompt' => 'Login',
            'role' => 'Admin',
            'tenant' => 'Tenant123',
            'state' => 'state456',
            'redirect_uri' => '/redirect'
        ];

        $_COOKIE['authName'] = $this->crypto->encrypt([
            'isLoggedIn' => true,
            'authCookie' => ['sub' => 'user123', 'iat' => time()]
        ]);

        $url = $this->invite->generateInviteUrl();

        $expectedUrl = "https://wallet.hello.coop/invite?app_name=MyApp&prompt=Login&role=Admin&tenant=Tenant123&state=state456&inviter=user123&client_id=valid_client_id&initiate_login_uri=https%3A%2F%2Fmy-domain&return_uri=https%3A%2F%2Fexample.com";

        $this->assertSame($expectedUrl, $url);
    }
}
