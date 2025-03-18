<?php

declare(strict_types=1);

namespace HelloCoop\Type\Command;

use InvalidArgumentException;

enum Command: string
{
    case METADATA = 'metadata';
    case UNAUTHORIZE = 'unauthorize';
    case ACTIVATE = 'activate';
    case SUSPEND = 'suspend';
    case REACTIVATE = 'reactivate';
    case ARCHIVE = 'archive';
    case RESTORE = 'restore';
    case DELETE = 'delete';
    case AUDIT_TENANT = 'audit_tenant';
    case UNAUTHORIZE_TENANT = 'unauthorize_tenant';
    case SUSPEND_TENANT = 'suspend_tenant';
    case ARCHIVE_TENANT = 'archive_tenant';
    case DELETE_TENANT = 'delete_tenant';
}

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
    ) {}

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            iss: $data['iss'] ?? '',
            sub: $data['sub'] ?? '',
            command: Command::tryFrom($data['command']) ?? throw new InvalidArgumentException('Invalid command'),
            tenant: $data['tenant'] ?? null,
            groups: $data['groups'] ?? null
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

