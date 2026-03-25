<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Exceptions;

use Modules\Shared\Domain\Exceptions\DomainException;

final class InvoiceMustBeInSendingStatusToBeMarkedAsDeliveredException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Only invoices in sending status can be marked as delivered.');
    }
}
