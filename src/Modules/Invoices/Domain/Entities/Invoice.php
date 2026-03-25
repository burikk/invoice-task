<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Entities;

use Modules\Invoices\Domain\Enums\StatusEnum;
use Modules\Invoices\Domain\Exceptions\InvoiceMustBeInDraftStatusToAddProductLinesException;
use Modules\Invoices\Domain\Exceptions\InvoiceMustBeInDraftStatusToBeSentException;
use Modules\Invoices\Domain\Exceptions\InvoiceMustBeInSendingStatusToBeMarkedAsDeliveredException;
use Modules\Invoices\Domain\Exceptions\InvoiceMustContainProductLinesToBeSentException;
use Modules\Invoices\Domain\Exceptions\InvoiceProductLinesMustHavePositiveQuantityAndPriceException;
use Modules\Invoices\Domain\ValueObjects\CustomerEmail;
use Modules\Invoices\Domain\ValueObjects\CustomerName;
use Modules\Invoices\Domain\ValueObjects\InvoiceId;

final class Invoice
{
    private function __construct(
        private readonly InvoiceId $id,
        private CustomerName $customerName,
        private CustomerEmail $customerEmail,
        private StatusEnum $status,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
        /** @var InvoiceProductLine[] */
        private array $productLines = [],
    )
    {
    }

    public function invoiceId(): InvoiceId
    {
        return $this->id;
    }

    public function customerName(): CustomerName
    {
        return $this->customerName;
    }

    public function customerEmail(): CustomerEmail
    {
        return $this->customerEmail;
    }

    public function status(): StatusEnum
    {
        return $this->status;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @return InvoiceProductLine[]
     */
    public function productLines(): array
    {
        return $this->productLines;
    }

    public static function create(InvoiceId $id, CustomerName $customerName, CustomerEmail $customerEmail): self
    {
        $now = new \DateTimeImmutable();

        return new self($id, $customerName, $customerEmail, StatusEnum::Draft, $now, $now);
    }

    public static function reconstitute(
        InvoiceId $id,
        CustomerName $customerName,
        CustomerEmail $customerEmail,
        StatusEnum $status,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
        array $productLines
    ): self {
        return new self($id, $customerName, $customerEmail, $status, $createdAt, $updatedAt, $productLines);
    }

    public function addProductLine(InvoiceProductLine $productLine): void
    {
        if ($this->status !== StatusEnum::Draft) {
            throw new InvoiceMustBeInDraftStatusToAddProductLinesException();
        }

        $this->productLines[] = $productLine;
    }

    public function send(): void
    {
        if ($this->status !== StatusEnum::Draft) {
            throw new InvoiceMustBeInDraftStatusToBeSentException();
        }

        if ($this->productLines === []) {
            throw new InvoiceMustContainProductLinesToBeSentException();
        }

        $this->status = StatusEnum::Sending;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function markAsDelivered(): void
    {
        if ($this->status !== StatusEnum::Sending) {
            throw new InvoiceMustBeInSendingStatusToBeMarkedAsDeliveredException();
        }

        $this->status = StatusEnum::SentToClient;
        $this->updatedAt = new \DateTimeImmutable();
    }
}
