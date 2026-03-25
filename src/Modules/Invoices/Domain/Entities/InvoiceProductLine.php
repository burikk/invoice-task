<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Entities;

use Modules\Invoices\Domain\ValueObjects\Price;
use Modules\Invoices\Domain\ValueObjects\ProductLineId;
use Modules\Invoices\Domain\ValueObjects\ProductLineName;
use Modules\Invoices\Domain\ValueObjects\Quantity;

final class InvoiceProductLine
{
    private function __construct(
        private readonly ProductLineId $id,
        private ProductLineName $productName,
        private Price $price,
        private Quantity $quantity,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    )
    {
    }

    public function productLineId(): ProductLineId
    {
        return $this->id;
    }

    public function productName(): ProductLineName
    {
        return $this->productName;
    }

    public function price(): Price
    {
        return $this->price;
    }

    public function quantity(): Quantity
    {
        return $this->quantity;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public static function create(
        ProductLineId $productLineId,
        ProductLineName $productName,
        Price $price,
        Quantity $quantity,
    ): self
    {
        $now = new \DateTimeImmutable();

        return new self($productLineId, $productName, $price, $quantity, $now, $now);
    }

    public static function reconstitute(
        ProductLineId $productLineId,
        ProductLineName $productName,
        Price $price,
        Quantity $quantity,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
    ): self
    {
        return new self($productLineId, $productName, $price, $quantity, $createdAt, $updatedAt);
    }
}
