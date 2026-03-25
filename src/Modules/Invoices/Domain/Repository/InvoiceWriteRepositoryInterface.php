<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Repository;

use Modules\Invoices\Domain\Entities\Invoice;
use Modules\Invoices\Domain\ValueObjects\InvoiceId;

interface InvoiceWriteRepositoryInterface
{
    public function save(Invoice $invoice): void;

    public function findById(InvoiceId $id): ?Invoice;
}
