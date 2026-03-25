<?php

declare(strict_types=1);

namespace Modules\Shared\Infrastructure\Bus;

use Illuminate\Bus\Dispatcher;

abstract readonly class BaseBus
{
    public function __construct(protected Dispatcher $dispatcher)
    {
    }

    public function register(array $map): void
    {
        $this->dispatcher->map($map);
    }
}
