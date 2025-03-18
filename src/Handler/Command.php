<?php

declare(strict_types=1);

namespace HelloCoop\Handler;

use HelloCoop\Type\Command\Command as CommandEnum;
use HelloCoop\Type\Command\CommandClaims;
use HelloCoop\Type\Command\MetadataResponse;
use HelloCoop\HelloResponse\HelloResponseInterface;
use HelloCoop\HelloRequest\HelloRequestInterface;
use HelloCoop\Config\ConfigInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use HelloCoop\Lib\IssuerRegistry;
use HelloCoop\Utils\PackageMetadata;
use InvalidArgumentException;

class Command
{
    private HelloResponseInterface $helloResponse;
    private HelloRequestInterface $helloRequest;
    private ConfigInterface $config;

    public function __construct(
        HelloRequestInterface $helloRequest,
        HelloResponseInterface $helloResponse,
        ConfigInterface $config
    ) {
        $this->helloRequest = $helloRequest;
        $this->helloResponse = $helloResponse;
        $this->config = $config;
    }

    /**
     * @return array<string, mixed>|false
     */
    public function verifyCommandToken(string $commandToken): array|false
    {
        $issuers = IssuerRegistry::getIssuers();

        $parts = explode('.', $commandToken);
        if (count($parts) !== 3) {
            return false;
        }

        try {
            $payloadJson = base64_decode($parts[1], true);
            if ($payloadJson === false) {
                error_log('commands.verifyCommandToken: invalid base64 encoding');
                return false;
            }

            $payload = json_decode($payloadJson, true);
            if ($payload === null) {
                error_log('commands.verifyCommandToken: invalid JSON decoding');
                return false;
            }

            if (!isset($payload['iss'])) {
                error_log('commands.verifyCommandToken: missing issuer');
                return false;
            }

            $iss = (string) $payload['iss'];

            if (!isset($issuers[$iss])) {
                error_log("commands.verifyCommandToken: unknown issuer - $iss");
                return false;
            }

            $client = new Client();
            $response = $client->post($issuers[$iss]->introspection_endpoint, [
                'form_params' => [
                    'token' => $commandToken,
                    'client_id' => $this->config->getClientId() ?? 'test-app',
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            error_log('error verifying command token: ' . $e->getMessage());
            return false;
        }
    }

    public function handleMetadata(CommandClaims $claims): void
    {
        $metadata = PackageMetadata::getMetadata();

        $metadataResponse = new MetadataResponse(
            context: [
                'package_name' => $metadata['name'],
                'package_version' => $metadata['version'],
                'iss' => $claims->iss,
                'tenant' => $claims->tenant ?? null,
            ],
            commands_uri: $this->config->getRedirectURI() ?? 'unknown',
            commands_supported: [CommandEnum::METADATA],
            commands_ttl: 0,
            client_id: $this->config->getClientId() ?? 'unknown'
        );

        $this->helloResponse->json($metadataResponse->toArray());
    }

    /**
     * @param array<string, mixed> $params
     */
    public function handleCommand(array $params): void
    {
        if ($this->helloRequest->has('command_token') === false) {
            $this->helloResponse->setStatusCode(500);
            return;
        }

        $commandToken = $this->helloRequest->fetch('command_token');
        $claims = $this->verifyCommandToken($commandToken);
        if (!$claims) {
            $this->helloResponse->setStatusCode(400);
            error_log('invalid command token');
            $this->helloResponse->json([
                'error' => 'invalid_request',
                'error_description' => 'invalid command token',
            ]);
            return;
        }

        $command = CommandEnum::tryFrom($claims['command']) ?? null;
        if (!$command) {
            $this->helloResponse->setStatusCode(400);
            $this->helloResponse->json(['error' => 'unsupported_command']);
            return;
        }

        $commandClaims = new CommandClaims(
            iss: $claims['iss'],
            sub: $claims['sub'],
            command: $command,
            tenant: $claims['tenant'] ?? null,
            groups: $claims['groups'] ?? null
        );

        $handler = $this->config->getCommandHandler();

        if ($handler instanceof CommandHandlerInterface) {
            $handler->handleCommand($commandClaims);
            return;
        }

        if ($commandClaims->command === CommandEnum::METADATA) {
            $this->handleMetadata($commandClaims);
            return;
        }

        $this->helloResponse->setStatusCode(400);
        $this->helloResponse->json(['error' => 'unsupported_command']);
    }
}
