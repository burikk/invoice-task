<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Exceptions;

use Modules\Shared\Domain\Exceptions\DomainException;

final class PriceMustBePositiveIntegerException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Price must be a positive integer');
    }
}
