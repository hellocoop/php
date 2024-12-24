<?php

namespace HelloCoop\Tests\Traits;

use PHPUnit\Framework\MockObject\MockObject;
use HelloCoop\HelloRequest\HelloRequestInterface;
use HelloCoop\HelloResponse\HelloResponseInterface;
use HelloCoop\Config\ConfigInterface;
use HelloCoop\Lib\Crypto;
use HelloCoop\Lib\TokenFetcher;
use HelloCoop\Lib\TokenParser;

trait ServiceMocksTrait
{
    /** @var MockObject & HelloRequestInterface */
    protected $helloRequestMock;

    /** @var MockObject & HelloResponseInterface */
    protected $helloResponseMock;

    /** @var MockObject & ConfigInterface */
    protected $configMock;

    /** @var Crypto */
    protected Crypto $crypto;

    /** @var MockObject & TokenFetcher */
    protected $tokenFetcherMock;

    /** @var MockObject & TokenParser */
    protected $tokenParserMock;

    /**
     * Set up all service mocks and their configurations.
     */
    protected function setUpServiceMocks(): void
    {
        // Create mocks
        $this->helloRequestMock = $this->createMock(HelloRequestInterface::class);
        $this->helloResponseMock = $this->createMock(HelloResponseInterface::class);
        $this->configMock = $this->createMock(ConfigInterface::class);
        $this->tokenFetcherMock = $this->createMock(TokenFetcher::class);
        $this->tokenParserMock = $this->createMock(TokenParser::class);

        // Configure ConfigInterface mock
        $this->configMock->method('getSecret')
            ->willReturn('1234567890abcdef1234567890abcdef1234567890abcdef1234567890abcdef');
        $this->configMock->method('getCookies')
            ->willReturn([
                'authName' => 'authName',
                'oidcName' => 'oidcName',
            ]);
        $this->configMock->method('getClientId')
            ->willReturn('valid_client_id');
        $this->configMock->method('getRedirectURI')
            ->willReturn('https//my-domain');

        $this->configMock->method('getHelloDomain')->willReturn('hello.coop');

        // Configure HelloRequestInterface mock
        $this->helloRequestMock->method('fetch')
            ->willReturnCallback(function ($key) {
                return $_GET[$key] ?? $_POST[$key] ?? null;
            });

        $this->helloRequestMock->method('fetchMultiple')
        ->willReturnCallback(function ($keys) {
            $result = [];
            foreach ($keys as $key) {
                $result[$key] = $this->helloRequestMock->fetch($key);
            }
            return $result;
        });

        $this->helloRequestMock->method('getMethod')
            ->willReturnCallback(function () {
                return $_SERVER['REQUEST_METHOD'];
            });

        $this->helloRequestMock->method('getCookie')
            ->willReturnCallback(function ($key) {
                return $_COOKIE[$key] ?? null;
            });

        // Instantiate the Crypto object with the secret from the config mock
        $this->crypto = new Crypto($this->configMock->getSecret());

        $_COOKIE = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    /**
     * @param object $object
     * @param string $propertyName
     * @param mixed $mock
     * @return void
     */
    private function replaceLazyLoadedProperty(object $object, string $propertyName, $mock): void
    {
        $reflection = new \ReflectionClass($object);

        if (!$reflection->hasProperty($propertyName)) {
            throw new \LogicException("Property '{$propertyName}' does not exist in the class.");
        }

        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $mock);
    }
}
