<?php

namespace Modules\Invoices\Domain\Exceptions;

use Modules\Shared\Domain\Exceptions\DomainException;

final class CustomerEmailCannotBeEmptyException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Customer email cannot be empty');
    }
}
