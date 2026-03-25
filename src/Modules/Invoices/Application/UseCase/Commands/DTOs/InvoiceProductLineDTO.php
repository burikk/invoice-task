<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\UseCase\Commands\DTOs;

final readonly class InvoiceProductLineDTO
{
    public function __construct(
        public string $name,
        public int $quantity,
        public int $price,
    ) {
    }
}
