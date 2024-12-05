<?php

namespace HelloCoop\Tests\HelloRespose;

use PHPUnit\Framework\TestCase;
use HelloCoop\HelloResponse\HelloResponse;
use phpmock\phpunit\PHPMock;

class HelloResponseTest extends TestCase
{
    use PHPMock;

    private HelloResponse $response;

    protected function setUp(): void
    {
        $this->response = new HelloResponse();
    }

    public function testSetHeader()
    {
        $headerMock = $this->getFunctionMock('HelloCoop\HelloResponse', 'header');
        $headerMock->expects($this->once())
                   ->with('Content-Type: application/json', true);

        $this->response->setHeader('Content-Type', 'application/json');
    }

    public function testSetHeaderWithArrayValue()
    {
        $headerMock = $this->getFunctionMock('HelloCoop\HelloResponse', 'header');
        $headerMock->expects($this->once())
                   ->with('X-Custom-Header: value1, value2', true);

        $this->response->setHeader('X-Custom-Header', ['value1', 'value2']);
    }

    public function testSetCookie()
    {
        $setCookieMock = $this->getFunctionMock('HelloCoop\HelloResponse', 'setcookie');
        $setCookieMock->expects($this->once())
                      ->with(
                          'test_cookie',
                          'test_value',
                          $this->callback(function ($options) {
                              return $options['expires'] === 0 &&
                                     $options['path'] === '/' &&
                                     $options['domain'] === '' &&
                                     $options['secure'] === false &&
                                     $options['httponly'] === true &&
                                     $options['samesite'] === 'Lax';
                          })
                      );

        $this->response->setCookie('test_cookie', 'test_value');
    }

    public function testDeleteCookie()
    {
        $setCookieMock = $this->getFunctionMock('HelloCoop\HelloResponse', 'setcookie');
        $setCookieMock->expects($this->once())
                      ->with(
                          'delete_cookie',
                          '',
                          $this->callback(function ($options) {
                              return $options['expires'] < time(); // Expired cookie.
                          })
                      );

        $this->response->deleteCookie('delete_cookie');
    }
}
