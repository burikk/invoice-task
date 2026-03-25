<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\ValueObjects;

use Modules\Invoices\Domain\Exceptions\QuantityMustBePositiveIntegerException;

final readonly class Quantity
{
    private function __construct(public int $value)
    {
        if ($value < 1) {
            throw new QuantityMustBePositiveIntegerException();
        }
    }

    public static function create(int $value): self
    {
        return new self($value);
    }
}
