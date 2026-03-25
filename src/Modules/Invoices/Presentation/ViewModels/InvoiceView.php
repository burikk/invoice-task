<?php

declare(strict_types=1);

namespace Modules\Invoices\Presentation\ViewModels;

use Modules\Shared\Application\Bus\Query\QueryResultInterface;

final readonly class InvoiceView implements QueryResultInterface
{
    public int $totalPrice;

    /**
     * @param InvoiceProductLineView[] $productLines
     */
    public function __construct(
        public string $id,
        public string $status,
        public string $customerName,
        public string $customerEmail,
        public array $productLines,
    ) {
        $this->totalPrice = array_sum(
            array_map(fn(InvoiceProductLineView $line) => $line->totalUnitPrice, $this->productLines)
        );
    }
}
