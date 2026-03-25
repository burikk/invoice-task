<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Invoices\Domain\ValueObjects;

use Modules\Invoices\Domain\ValueObjects\ProductLineId;
use Modules\Shared\Domain\Exceptions\InvalidUuidFormatException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class ProductLineIdTest extends TestCase
{
    #[DataProvider('validUuidProvider')]
    public function testCreatesProductLineIdFromValidString(string $uuid): void
    {
        $productLineId = ProductLineId::fromString($uuid);

        self::assertSame($uuid, $productLineId->toString());
    }

    #[DataProvider('invalidUuidProvider')]
    public function testThrowsExceptionWhenUuidIsInvalid(string $uuid): void
    {
        $this->expectException(InvalidUuidFormatException::class);

        ProductLineId::fromString($uuid);
    }

    public function testGeneratesUniqueProductLineId(): void
    {
        $id1 = ProductLineId::generate();
        $id2 = ProductLineId::generate();

        self::assertNotSame($id1->toString(), $id2->toString());
    }

    public function testCreatesProductLineIdFromUuidInterface(): void
    {
        $uuid = Uuid::uuid4();
        $productLineId = ProductLineId::fromUuid($uuid);

        self::assertSame($uuid->toString(), $productLineId->toString());
    }

    public function testConvertsToUuidInterface(): void
    {
        $uuid = Uuid::uuid4()->toString();
        $productLineId = ProductLineId::fromString($uuid);

        self::assertSame($uuid, $productLineId->toUuid()->toString());
    }

    #[DataProvider('equalityProvider')]
    public function testEquality(string $uuid1, string $uuid2, bool $expectedEqual): void
    {
        $id1 = ProductLineId::fromString($uuid1);
        $id2 = ProductLineId::fromString($uuid2);

        self::assertSame($expectedEqual, $id1->equals($id2));
    }

    public static function validUuidProvider(): array
    {
        return [
            'uuid v4' => [Uuid::uuid4()->toString()],
            'fixed uuid' => ['550e8400-e29b-41d4-a716-446655440000'],
        ];
    }

    public static function invalidUuidProvider(): array
    {
        return [
            'empty string' => [''],
            'plain string' => ['not-a-uuid'],
            'partial uuid' => ['550e8400-e29b'],
        ];
    }

    public static function equalityProvider(): array
    {
        return [
            'same uuid' => [
                '550e8400-e29b-41d4-a716-446655440000',
                '550e8400-e29b-41d4-a716-446655440000',
                true,
            ],
            'different uuid' => [
                '550e8400-e29b-41d4-a716-446655440000',
                '660e8400-e29b-41d4-a716-446655440000',
                false,
            ],
        ];
    }
}
