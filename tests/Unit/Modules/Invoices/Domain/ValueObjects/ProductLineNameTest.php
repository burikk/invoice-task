<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Invoices\Domain\ValueObjects;

use Modules\Invoices\Domain\ValueObjects\ProductLineName;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ProductLineNameTest extends TestCase
{
    #[DataProvider('validNameProvider')]
    public function testCreatesProductLineNameWithValidName(string $name): void
    {
        $productLineName = ProductLineName::create($name);

        self::assertSame($name, $productLineName->value);
    }

    public function testThrowsExceptionWhenNameIsEmpty(): void
    {
        $this->expectException(\Throwable::class);

        ProductLineName::create('');
    }

    public static function validNameProvider(): array
    {
        return [
            'simple name' => ['Widget'],
            'name with spaces' => ['Premium Widget'],
            'name with numbers' => ['Widget v2.0'],
            'long name' => ['Enterprise Grade Premium Widget Package'],
        ];
    }
}
