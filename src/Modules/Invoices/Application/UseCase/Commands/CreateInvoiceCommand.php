<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\UseCase\Commands;

use Modules\Invoices\Application\UseCase\Commands\DTOs\InvoiceProductLineDTO;
use Modules\Shared\Application\Bus\Command\CommandInterface;

final readonly class CreateInvoiceCommand implements CommandInterface
{
    /**
     * @param InvoiceProductLineDTO[] $productLines
     */
    public function __construct(
        public string $customerName,
        public string $customerEmail,
        public array $productLines,
    ) {
    }
}
