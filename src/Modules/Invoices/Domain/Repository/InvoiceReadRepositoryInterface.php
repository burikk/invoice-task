<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Repository;

use Modules\Invoices\Presentation\ViewModels\InvoiceView;

interface InvoiceReadRepositoryInterface
{
    public function findById(string $id): ?InvoiceView;
}
