<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\ValueObjects;

use Modules\Invoices\Domain\Exceptions\ProductNameCannotBeEmptyException;

final readonly class ProductLineName
{
    private function __construct(public string $value)
    {
        if ($this->value === '') {
            throw new ProductNameCannotBeEmptyException();
        }
    }

    public static function create(string $value): self
    {
        return new self($value);
    }
}
