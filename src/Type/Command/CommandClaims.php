<?php

declare(strict_types=1);

namespace HelloCoop\Type\Command;

use InvalidArgumentException;

final class CommandClaims
{
    /**
     * @param array<string>|null $groups
     */
    public function __construct(
        public readonly string $iss,
        public readonly string $sub,
        public readonly Command $command,
        public readonly ?string $tenant = null,
        public readonly ?array $groups = null
    ) {
    }

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $iss = isset($data['iss']) ? (string) $data['iss'] : '';
        $sub = isset($data['sub']) ? (string) $data['sub'] : '';
        $commandValue = isset($data['command']) ? (string) $data['command'] : '';
        $command = Command::tryFrom($commandValue) ?? throw new InvalidArgumentException('Invalid command');
        $tenant = isset($data['tenant']) ? (string) $data['tenant'] : null;
        $groups = isset($data['groups']) ? (array) $data['groups'] : null;

        return new self(
            iss: $iss,
            sub: $sub,
            command: $command,
            tenant: $tenant,
            groups: $groups
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'iss' => $this->iss,
            'sub' => $this->sub,
            'command' => $this->command->value,
            'tenant' => $this->tenant,
            'groups' => $this->groups,
        ];
    }
}
