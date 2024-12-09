<?php

namespace HelloCoop\Tests\Handler;

use PHPUnit\Framework\TestCase;
use HelloCoop\Handler\Invite;
use HelloCoop\Tests\Traits\ServiceMocksTrait;

class InviteTest extends TestCase
{
    use ServiceMocksTrait;

    private Invite $invite;

    public function setUp(): void
    {
        parent::setUp();
        $this->setUpServiceMocks();

        $this->invite = new Invite(
            $this->helloRequestMock,
            $this->helloResponseMock,
            $this->configMock
        );
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

        // Test the URL generation
        $url = $this->invite->generateInviteUrl();

        // Assert the URL is valid
        $this->assertTrue(
            filter_var($url, FILTER_VALIDATE_URL) !== false,
            "The URL is not valid."
        );

        // Define the expected URL
        $expectedUrl = "https://wallet.hello.coop/invite?app_name=MyApp&prompt=Login&role=Admin&tenant=Tenant123&state=state456&inviter=user123&client_id=valid_client_id&initiate_login_uri=https%2F%2Fmy-domain&return_uri=https%3A%2F%2Fexample.com";

        // Assert that the generated URL matches the expected one
        $this->assertSame($expectedUrl, $url);
    }
}
