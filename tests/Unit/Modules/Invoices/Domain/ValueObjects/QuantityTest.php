<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Invoices\Domain\ValueObjects;

use Modules\Invoices\Domain\Exceptions\QuantityMustBePositiveIntegerException;
use Modules\Invoices\Domain\ValueObjects\Quantity;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class QuantityTest extends TestCase
{
    #[DataProvider('validQuantityProvider')]
    public function testCreatesQuantityWithValidValue(int $quantity): void
    {
        $quantityResult = Quantity::create($quantity);

        self::assertSame($quantity, $quantityResult->value);
    }

    #[DataProvider('invalidQuantityProvider')]
    public function testThrowsExceptionWhenQuantityIsInvalid(int $quantity): void
    {
        $this->expectException(QuantityMustBePositiveIntegerException::class);

        Quantity::create($quantity);
    }

    public static function validQuantityProvider(): array
    {
        return [
            'one' => [1],
            'small quantity' => [5],
            'large quantity' => [1000],
        ];
    }

    public static function invalidQuantityProvider(): array
    {
        return [
            'zero' => [0],
            'negative' => [-1],
            'large negative' => [-100],
        ];
    }
}
