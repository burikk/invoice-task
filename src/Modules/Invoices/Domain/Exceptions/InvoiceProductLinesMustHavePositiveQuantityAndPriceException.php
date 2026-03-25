<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Exceptions;

use Modules\Shared\Domain\Exceptions\DomainException;

final class InvoiceProductLinesMustHavePositiveQuantityAndPriceException extends DomainException
{
    public function __construct()
    {
        parent::__construct('All product lines must have positive quantity and unit price.');
    }
}
