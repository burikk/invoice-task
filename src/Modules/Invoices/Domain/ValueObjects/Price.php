<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\ValueObjects;

use Modules\Invoices\Domain\Exceptions\PriceMustBePositiveIntegerException;

final readonly class Price
{
    private function __construct(public int $value)
    {
        if ($value < 1) {
            throw new PriceMustBePositiveIntegerException();
        }
    }

    public static function create(int $value): self
    {
        return new self($value);
    }

    public function add(Price $other): Price
    {
        return new Price($this->value + $other->value);
    }

    public function equals(Price $other): bool
    {
        return $this->value === $other->value;
    }
}
