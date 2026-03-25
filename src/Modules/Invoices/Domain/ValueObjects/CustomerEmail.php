<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\ValueObjects;

use Modules\Invoices\Domain\Exceptions\CustomerEmailCannotBeEmptyException;
use Modules\Invoices\Domain\Exceptions\CustomerEmailMustBeValidException;

final readonly class CustomerEmail
{
    private function __construct(public string $value)
    {
        if ($this->value === '') {
            throw new CustomerEmailCannotBeEmptyException();
        }

        if (!filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            throw new CustomerEmailMustBeValidException();
        }
    }

    public static function create(string $value): self
    {
        return new self($value);
    }
}
