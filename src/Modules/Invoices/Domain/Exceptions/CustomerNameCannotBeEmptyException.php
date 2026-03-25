<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Exceptions;

use Modules\Shared\Domain\Exceptions\DomainException;

final class CustomerNameCannotBeEmptyException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Customer name cannot be empty.');
    }
}
