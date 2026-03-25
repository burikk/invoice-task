<?php

declare(strict_types=1);

namespace Modules\Shared\Application\Bus\Command;

interface CommandBusInterface
{
    public function execute(CommandInterface $command): void;

    public function register(array $map): void;
}
