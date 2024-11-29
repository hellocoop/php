<?php

namespace HelloCoop\Tests;

use HelloCoop\Config\HelloConfig;
use PHPUnit\Framework\TestCase;
use HelloCoop\HelloClient;

class HelloClientTest extends TestCase
{
    protected string $cookieName;
    protected HelloClient $client;
    private $configMock;

    protected function setUp(): void
    {
        // Simulate the config setting for the cookie name (this could be dynamic)
        $this->cookieName = 'hello_user';

        // Simulate a fresh environment by clearing cookies
        $_COOKIE = [];

        $this->configMock = $this->createMock(HelloConfig::class);
        // Initialize the client
        $this->client = new HelloClient($this->configMock);
    }

    public function testGetAuthBeforeLogin(): void
    {
        // Test the state before any login
        $auth = $this->client->getAuth();

        // Assert that user is not logged in
        $this->assertEquals(['isLoggedIn' => false], $auth);
    }

    public function testGetAuthAfterLogin(): void
    {
        // Simulate login (this would normally set the cookie internally in a real scenario)
        $this->client->login();

        // Simulate the cookie being set after login
        $userData = [
            "isLoggedIn" => true,
            "sub" => "sub_vvCgtpv35lDgQpHtxmpvmnxK_2nZ",
            "iat" => 1699234659,
            "name" => "Dick Hardt",
            "picture" => "https://pictures.hello.coop/r/7a160eed-46bf-48e2-a909-161745535895.png",
            "email" => "dick.hardt@hello.coop"
        ];

        // Manually set the cookie with the user data as the login action would do
        $_COOKIE[$this->cookieName] = json_encode($userData);

        // After login, getAuth should return the user data
        $auth = $this->client->getAuth();

        // Assert the auth data is correct
        $this->assertEquals($userData, $auth);
    }

    public function testGetAuthAfterLogout(): void
    {
        // Simulate login by setting a cookie
        $userData = [
            "isLoggedIn" => true,
            "sub" => "sub_vvCgtpv35lDgQpHtxmpvmnxK_2nZ"
        ];

        // Manually set the cookie to simulate login
        $_COOKIE[$this->cookieName] = json_encode($userData);

        // Call the logout method
        $this->client->logout();

        // After logout, getAuth should return isLoggedIn: false
        $auth = $this->client->getAuth();

        // Assert that the user is logged out
        $this->assertEquals(['isLoggedIn' => false], $auth);

        // Assert that the cookie has been cleared (i.e., the configured cookie name should not exist)
        $this->assertArrayNotHasKey($this->cookieName, $_COOKIE);
    }
}
