<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Exceptions;

use Modules\Shared\Domain\Exceptions\DomainException;

final class InvoiceMustContainProductLinesToBeSentException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Invoice must contain at least one product line to be sent.');
    }
}
