<?php

namespace HelloCoop\Tests\RequestParamFetcher;

use PHPUnit\Framework\TestCase;
use HelloCoop\RequestParamFetcher\RequestParamFetcher;

class RequestParamFetcherTest extends TestCase
{
    protected function setUp(): void
    {
        // Backup the original superglobals and server
        $this->originalGet = $_GET;
        $this->originalPost = $_POST;
        $this->originalServer = $_SERVER;
    }

    protected function tearDown(): void
    {
        // Restore the original superglobals and server
        $_GET = $this->originalGet;
        $_POST = $this->originalPost;
        $_SERVER = $this->originalServer;
    }

    public function testFetchFromGet()
    {
        $_GET = ['param1' => 'value1'];

        $fetcher = new RequestParamFetcher();
        $result = $fetcher->fetch('param1');

        $this->assertEquals('value1', $result);
    }

    public function testFetchFromPost()
    {
        $_POST = ['param2' => 'value2'];

        $fetcher = new RequestParamFetcher();
        $result = $fetcher->fetch('param2');

        $this->assertEquals('value2', $result);
    }

    public function testFetchWithDefault()
    {
        $fetcher = new RequestParamFetcher();
        $result = $fetcher->fetch('nonexistent', 'default_value');

        $this->assertEquals('default_value', $result);
    }

    public function testFetchMultiple()
    {
        $_GET = ['param1' => 'value1'];
        $_POST = ['param2' => 'value2'];

        $fetcher = new RequestParamFetcher();
        $result = $fetcher->fetchMultiple(['param1', 'param2', 'param3']);

        $this->assertEquals([
            'param1' => 'value1',
            'param2' => 'value2',
            'param3' => null,
        ], $result);
    }

    public function testFetchHeader()
    {
        $_SERVER = [
            'HTTP_AUTHORIZATION' => 'Bearer token',
            'HTTP_X_CUSTOM_HEADER' => 'CustomValue',
        ];

        $fetcher = new RequestParamFetcher();
        $result = $fetcher->fetchHeader('Authorization');
        $customHeader = $fetcher->fetchHeader('X-Custom-Header');
        $nonexistentHeader = $fetcher->fetchHeader('Nonexistent-Header', 'default_value');

        $this->assertEquals('Bearer token', $result);
        $this->assertEquals('CustomValue', $customHeader);
        $this->assertEquals('default_value', $nonexistentHeader);
    }
}
