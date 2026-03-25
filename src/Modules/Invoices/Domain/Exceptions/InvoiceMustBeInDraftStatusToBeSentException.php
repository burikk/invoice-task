<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Exceptions;

use Modules\Shared\Domain\Exceptions\DomainException;

final class InvoiceMustBeInDraftStatusToBeSentException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Invoice can only be sent if it is in draft status.');
    }
}
