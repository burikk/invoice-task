<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\UseCase\Queries;

use Modules\Shared\Application\Bus\Query\QueryInterface;

final readonly class ViewInvoiceQuery implements QueryInterface
{
    public function __construct(public string $id)
    {
    }
}
