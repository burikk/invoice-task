<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Exceptions;

use Modules\Shared\Domain\Exceptions\DomainException;

final class CustomerEmailMustBeValidException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Customer email must be a valid email address');
    }
}
