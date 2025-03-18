<?php

declare(strict_types=1);

namespace HelloCoop\Handler;

use HelloCoop\Type\Command\CommandClaims;

interface CommandHandlerInterface
{
    public function handleCommand(CommandClaims $commandClaims): void;
}
