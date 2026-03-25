<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Invoices\Domain\Entities;

use Modules\Invoices\Domain\Entities\InvoiceProductLine;
use Modules\Invoices\Domain\ValueObjects\Price;
use Modules\Invoices\Domain\ValueObjects\ProductLineId;
use Modules\Invoices\Domain\ValueObjects\ProductLineName;
use Modules\Invoices\Domain\ValueObjects\Quantity;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class InvoiceProductLineTest extends TestCase
{
    public function testCreateSetsCreatedAtAndUpdatedAtToSameValue(): void
    {
        $line = InvoiceProductLine::create(
            ProductLineId::generate(),
            ProductLineName::create('Widget'),
            Price::create(50),
            Quantity::create(2),
        );

        self::assertEquals($line->createdAt(), $line->updatedAt());
    }

    public function testReconstituteRestoresAllProperties(): void
    {
        $id = ProductLineId::generate();
        $name = ProductLineName::create('Gadget');
        $price = Price::create(200);
        $quantity = Quantity::create(5);
        $createdAt = new \DateTimeImmutable('2025-01-01 10:00:00');
        $updatedAt = new \DateTimeImmutable('2025-06-15 14:30:00');

        $line = InvoiceProductLine::reconstitute($id, $name, $price, $quantity, $createdAt, $updatedAt);

        self::assertTrue($line->productLineId()->equals($id));
        self::assertSame('Gadget', $line->productName()->value);
        self::assertSame(200, $line->price()->value);
        self::assertSame(5, $line->quantity()->value);
        self::assertSame($createdAt, $line->createdAt());
        self::assertSame($updatedAt, $line->updatedAt());
    }

    public function testReconstitutePreservesExactTimestamps(): void
    {
        $createdAt = new \DateTimeImmutable('2024-03-10 08:00:00');
        $updatedAt = new \DateTimeImmutable('2024-03-12 16:45:00');

        $line = InvoiceProductLine::reconstitute(
            ProductLineId::generate(),
            ProductLineName::create('Part'),
            Price::create(10),
            Quantity::create(1),
            $createdAt,
            $updatedAt,
        );

        self::assertNotEquals($line->createdAt(), $line->updatedAt());
        self::assertSame($createdAt, $line->createdAt());
        self::assertSame($updatedAt, $line->updatedAt());
    }

    #[DataProvider('productLineDataProvider')]
    public function testCreatesWithVariousValidInputs(string $name, int $price, int $quantity): void
    {
        $line = InvoiceProductLine::create(
            ProductLineId::generate(),
            ProductLineName::create($name),
            Price::create($price),
            Quantity::create($quantity),
        );

        self::assertSame($name, $line->productName()->value);
        self::assertSame($price, $line->price()->value);
        self::assertSame($quantity, $line->quantity()->value);
    }

    public static function productLineDataProvider(): array
    {
        return [
            'minimal values' => ['A', 1, 1],
            'typical product' => ['Premium Widget', 1500, 10],
            'expensive single item' => ['Enterprise License', 999999, 1],
            'cheap bulk item' => ['Bolt', 1, 5000],
        ];
    }

    public function testTwoProductLinesCreatedWithSameDataHaveDifferentIdentities(): void
    {
        $name = ProductLineName::create('Widget');
        $price = Price::create(100);
        $quantity = Quantity::create(2);

        $line1 = InvoiceProductLine::create(ProductLineId::generate(), $name, $price, $quantity);
        $line2 = InvoiceProductLine::create(ProductLineId::generate(), $name, $price, $quantity);

        self::assertFalse($line1->productLineId()->equals($line2->productLineId()));
    }
}
