<?php

declare(strict_types=1);

namespace Modules\Shared\Infrastructure\Bus\CommandBus;

use Modules\Shared\Application\Bus\Command\CommandBusInterface;
use Modules\Shared\Application\Bus\Command\CommandInterface;
use Modules\Shared\Infrastructure\Bus\BaseBus;

final readonly class CommandBus extends BaseBus implements CommandBusInterface
{
    public function execute(CommandInterface $command): void
    {
        $this->dispatcher->dispatch($command);
    }
}
