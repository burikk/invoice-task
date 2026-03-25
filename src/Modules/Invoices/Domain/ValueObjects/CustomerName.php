<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\ValueObjects;

use Modules\Invoices\Domain\Exceptions\CustomerNameCannotBeEmptyException;

final readonly class CustomerName
{
    private function __construct(public string $value)
    {
        if ($this->value === '') {
            throw new CustomerNameCannotBeEmptyException();
        }
    }

    public static function create(string $value): self
    {
        return new self($value);
    }
}
