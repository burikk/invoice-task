<?php

declare(strict_types=1);

namespace Modules\Shared\Infrastructure\Bus\QueryBus;

use Modules\Shared\Application\Bus\Query\QueryBusInterface;
use Modules\Shared\Application\Bus\Query\QueryInterface;
use Modules\Shared\Application\Bus\Query\QueryResultInterface;
use Modules\Shared\Infrastructure\Bus\BaseBus;

final readonly class QueryBus extends BaseBus implements QueryBusInterface
{
    public function execute(QueryInterface $query): QueryResultInterface
    {
        return $this->dispatcher->dispatch($query);
    }
}
