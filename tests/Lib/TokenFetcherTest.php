<?php

namespace HelloCoop\Tests\Lib;

use PHPUnit\Framework\TestCase;
use HelloCoop\Lib\TokenFetcher;
use HelloCoop\Utils\CurlWrapper;
use HelloCoop\Config\Constants;

class TokenFetcherTest extends TestCase
{
    public function testFetchTokenSuccess(): void
    {
        $curlMock = $this->createMock(CurlWrapper::class);
        $curlMock->method('init')->willReturn(true);
        $curlMock->method('setOpt')->willReturn(true);
        $curlMock->method('exec')->willReturn(json_encode(['id_token' => 'mocked_id_token']));
        $curlMock->method('getInfo')->willReturn(200);
        $curlMock->method('error')->willReturn('');

        $tokenFetcher = new TokenFetcher($curlMock);
        $result = $tokenFetcher->fetchToken([
            'code' => 'mocked_code',
            'code_verifier' => 'mocked_verifier',
            'client_id' => 'mocked_id',
            'redirect_uri' => 'mocked_redirect',
            'wallet' => Constants::$PRODUCTION_WALLET,
        ]);

        $this->assertEquals('mocked_id_token', $result);
    }

    public function testFetchTokenErrorResponse(): void
    {
        $curlMock = $this->createMock(CurlWrapper::class);
        $curlMock->method('exec')->willReturn(json_encode(['error' => 'mock_error']));
        $curlMock->method('getInfo')->willReturn(400);

        $tokenFetcher = new TokenFetcher($curlMock);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('mock_error');

        $tokenFetcher->fetchToken([
            'code' => 'mocked_code',
            'code_verifier' => 'mocked_verifier',
            'client_id' => 'mocked_id',
            'redirect_uri' => 'mocked_redirect',
        ]);
    }
}
