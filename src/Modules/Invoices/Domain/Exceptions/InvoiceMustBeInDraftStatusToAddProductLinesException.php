<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Exceptions;

use Modules\Shared\Domain\Exceptions\DomainException;

final class InvoiceMustBeInDraftStatusToAddProductLinesException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Product lines can only be added when the invoice is in draft status.');
    }
}
