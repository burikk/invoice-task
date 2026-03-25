<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\Exceptions;

use Modules\Shared\Application\Exceptions\ApplicationException;

final class InvoiceNotFoundException extends ApplicationException
{
    public function __construct()
    {
        parent::__construct('Invoice not found.');
    }
}
