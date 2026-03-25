<?php

namespace Modules\Invoices\Presentation\ViewModels;

use Modules\Shared\Application\Bus\Query\QueryResultInterface;

final readonly class InvoiceProductLineView implements QueryResultInterface
{
    public int $totalUnitPrice;

    public function __construct(
        public string $id,
        public string $productName,
        public int $quantity,
        public int $unitPrice,
    ) {
        $this->totalUnitPrice = $this->quantity * $this->unitPrice;
    }
}
