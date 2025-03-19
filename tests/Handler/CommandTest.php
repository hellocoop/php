<?php

declare(strict_types=1);

namespace HelloCoop\Tests\Handler;

use HelloCoop\Handler\Command;
use HelloCoop\HelloResponse\HelloResponseInterface;
use HelloCoop\HelloRequest\HelloRequestInterface;
use HelloCoop\Config\ConfigInterface;
use HelloCoop\Type\Command\Command as CommandEnum;
use HelloCoop\Type\Command\CommandClaims;
use HelloCoop\Type\Command\MetadataResponse;
use HelloCoop\Lib\IssuerRegistry;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class CommandTest extends TestCase
{
    private Command $command;
    private $helloRequest;
    private $helloResponse;
    private $config;
    
    protected function setUp(): void
    {
        $this->helloRequest = $this->createMock(HelloRequestInterface::class);
        $this->helloResponse = $this->createMock(HelloResponseInterface::class);
        $this->config = $this->createMock(ConfigInterface::class);
        
        $this->command = new Command(
            $this->helloRequest,
            $this->helloResponse,
            $this->config
        );
    }

    // public function testVerifyCommandTokenWithInvalidToken(): void
    // {
    //     $this->assertFalse($this->command->verifyCommandToken('invalid.token.format'));
    // }

    public function testVerifyCommandTokenWithValidToken(): void
    {
        $tokenPayload = base64_encode(json_encode(['iss' => 'https://issuer.hello.coop']));
        $commandToken = 'header.' . $tokenPayload . '.signature';
        
        $mockClient = $this->createMock(Client::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);

        $mockStream->method('getContents')->willReturn(json_encode(['command' => 'METADATA']));
        $mockResponse->method('getBody')->willReturn($mockStream);
        $mockClient->method('post')->willReturn($mockResponse);

        $this->assertIsArray($this->command->verifyCommandToken($commandToken));
    }

    // public function testHandleCommandWithoutToken(): void
    // {
    //     $this->helloRequest->method('has')->with('command_token')->willReturn(false);
    //     $this->helloResponse->expects($this->once())->method('setStatusCode')->with(500);
        
    //     $this->command->handleCommand();
    // }

    // public function testHandleCommandWithInvalidToken(): void
    // {
    //     $this->helloRequest->method('has')->with('command_token')->willReturn(true);
    //     $this->helloRequest->method('fetch')->with('command_token')->willReturn('invalid.token.format');
        
    //     $this->helloResponse->expects($this->once())->method('setStatusCode')->with(400);
        
    //     $this->command->handleCommand();
    // }
}
