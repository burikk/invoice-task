<?php

declare(strict_types=1);

namespace Modules\Shared\Application\Bus\Query;

interface QueryBusInterface
{
    public function execute(QueryInterface $query): QueryResultInterface;

    public function register(array $map): void;
}
