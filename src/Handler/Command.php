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
            error_log('Invalid token format');
            return false;
        }

        try {
            $payloadJson = base64_decode($parts[1], true);
            if ($payloadJson === false) {
                error_log('commands.verifyCommandToken: invalid base64 encoding');
                return false;
            }

            /**
             * @var array<string, mixed> $payload
             */
            $payload = json_decode($payloadJson, true);
            if (!is_array($payload)) {
                error_log('commands.verifyCommandToken: invalid JSON decoding');
                return false;
            }

            if (!isset($payload['iss'])) {
                error_log('commands.verifyCommandToken: missing issuer');
                return false;
            }

            /** @var string $iss */
            $iss = $payload['iss'];

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

            /** @var array<string, mixed> $decodedResponse */
            $decodedResponse = json_decode($response->getBody()->getContents(), true);
            return $decodedResponse;
        } catch (RequestException $e) {
            error_log('error verifying command token: ' . $e->getMessage());
            return false;
        }
    }

    //TODO: change the access rule to private
    public function handleMetadata(CommandClaims $claims): string
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

        return $this->helloResponse->json($metadataResponse->toArray());
    }

    public function handleCommand(): string
    {
        if ($this->helloRequest->has('command_token') === false) {
            $this->helloResponse->setStatusCode(500);
            $this->helloResponse->send();
        }

        /** @var string $commandToken */
        $commandToken = $this->helloRequest->fetch('command_token');
        /** @var array<string, mixed> $claims */
        $claims = $this->verifyCommandToken($commandToken);
        // Ensure claims is an array before accessing its keys
        if (!$claims || !is_array($claims)) {
            $this->helloResponse->setStatusCode(400);
            error_log('invalid command token');
            $this->helloResponse->json([
                'error' => 'invalid_request',
                'error_description' => 'invalid command token',
            ]);
            $this->helloResponse->send();
        }

        /** @var string $commandEnum */
        $commandEnum = $claims['command'];
        $command = CommandEnum::tryFrom($commandEnum) ?? '';
        if (!$command) {
            $this->helloResponse->setStatusCode(400);
            return $this->helloResponse->json(['error' => 'unsupported_command']);
        }

        /** @var string $iss */
        $iss = $claims['iss'];
        /** @var string $sub */
        $sub = $claims['sub'];
        /** @var string|null $tenant */
        $tenant = isset($claims['tenant']) ? $claims['tenant'] : null;
        /** @var array<string>|null $groups */
        $groups = isset($claims['groups']) ? $claims['groups'] : null;
        $commandClaims = new CommandClaims(
            iss: $iss,
            sub: $sub,
            command: $command,
            tenant: $tenant,
            groups: $groups
        );

        $handler = $this->config->getCommandHandler();

        if ($handler instanceof CommandHandlerInterface) {
            return $handler->handleCommand($commandClaims);
        }

        if ($commandClaims->command === CommandEnum::METADATA) {
            return $this->handleMetadata($commandClaims);
        }

        $this->helloResponse->setStatusCode(400);
        return $this->helloResponse->json(['error' => 'unsupported_command']);
    }
}
