<?php

namespace HelloCoop\Type\Command;

final class MetadataResponse
{
    public function __construct(
        public readonly array $context,
        public readonly string $commands_uri,
        public readonly array $commands_supported,
        public readonly int $commands_ttl,
        public readonly string $client_id
    ) {}

    public function toArray(): array
    {
        return [
            'context' => $this->context,
            'commands_uri' => $this->commands_uri,
            'commands_supported' => array_map(fn(Command $cmd) => $cmd->value, $this->commands_supported),
            'commands_ttl' => $this->commands_ttl,
            'client_id' => $this->client_id,
        ];
    }
}