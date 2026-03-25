<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Invoices\Domain\ValueObjects;

use Modules\Invoices\Domain\ValueObjects\Price;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PriceTest extends TestCase
{
    #[DataProvider('validPriceProvider')]
    public function testCreatesProductLineUnitPriceWithValidValue(int $price): void
    {
        $priceResult = Price::create($price);

        self::assertSame($price, $priceResult->value);
    }

    #[DataProvider('invalidPriceProvider')]
    public function testThrowsExceptionWhenPriceIsInvalid(int $price): void
    {
        $this->expectException(\Throwable::class);

        Price::create($price);
    }

    public static function validPriceProvider(): array
    {
        return [
            'one cent' => [1],
            'one dollar' => [100],
            'large price' => [999999],
        ];
    }

    public static function invalidPriceProvider(): array
    {
        return [
            'zero' => [0],
            'negative' => [-1],
            'large negative' => [-500],
        ];
    }
}
