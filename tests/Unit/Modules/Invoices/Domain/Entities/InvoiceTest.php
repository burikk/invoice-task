<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Invoices\Domain\Entities;

use Modules\Invoices\Domain\Entities\Invoice;
use Modules\Invoices\Domain\Entities\InvoiceProductLine;
use Modules\Invoices\Domain\Enums\StatusEnum;
use Modules\Invoices\Domain\Exceptions\InvoiceMustBeInDraftStatusToAddProductLinesException;
use Modules\Invoices\Domain\Exceptions\InvoiceMustBeInDraftStatusToBeSentException;
use Modules\Invoices\Domain\Exceptions\InvoiceMustBeInSendingStatusToBeMarkedAsDeliveredException;
use Modules\Invoices\Domain\Exceptions\InvoiceMustContainProductLinesToBeSentException;
use Modules\Invoices\Domain\ValueObjects\CustomerEmail;
use Modules\Invoices\Domain\ValueObjects\CustomerName;
use Modules\Invoices\Domain\ValueObjects\InvoiceId;
use Modules\Invoices\Domain\ValueObjects\Price;
use Modules\Invoices\Domain\ValueObjects\ProductLineId;
use Modules\Invoices\Domain\ValueObjects\ProductLineName;
use Modules\Invoices\Domain\ValueObjects\Quantity;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class InvoiceTest extends TestCase
{
    public function testCreateAlwaysStartsInDraftStatus(): void
    {
        $invoice = $this->createDraftInvoice();

        self::assertSame(StatusEnum::Draft, $invoice->status());
    }

    public function testCreateStartsWithEmptyProductLines(): void
    {
        $invoice = $this->createDraftInvoice();

        self::assertSame([], $invoice->productLines());
    }

    public function testReconstituteRestoresAllProperties(): void
    {
        $id = InvoiceId::generate();
        $name = CustomerName::create('Jane Doe');
        $email = CustomerEmail::create('jane@example.com');
        $createdAt = new \DateTimeImmutable('2025-01-01 10:00:00');
        $updatedAt = new \DateTimeImmutable('2025-06-15 14:30:00');
        $line = $this->createProductLine();

        $invoice = Invoice::reconstitute($id, $name, $email, StatusEnum::Sending, $createdAt, $updatedAt, [$line]);

        self::assertTrue($invoice->invoiceId()->equals($id));
        self::assertSame('Jane Doe', $invoice->customerName()->value);
        self::assertSame('jane@example.com', $invoice->customerEmail()->value);
        self::assertSame(StatusEnum::Sending, $invoice->status());
        self::assertSame($createdAt, $invoice->createdAt());
        self::assertSame($updatedAt, $invoice->updatedAt());
        self::assertCount(1, $invoice->productLines());
    }

    #[DataProvider('statusProvider')]
    public function testReconstituteAcceptsAnyStatus(StatusEnum $status): void
    {
        $invoice = Invoice::reconstitute(
            InvoiceId::generate(),
            CustomerName::create('Test'),
            CustomerEmail::create('test@example.com'),
            $status,
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
            [],
        );

        self::assertSame($status, $invoice->status());
    }

    public function testAddMultipleProductLinesInDraftStatus(): void
    {
        $invoice = $this->createDraftInvoice();

        $invoice->addProductLine($this->createProductLine('Widget', 100, 2));
        $invoice->addProductLine($this->createProductLine('Gadget', 200, 1));
        $invoice->addProductLine($this->createProductLine('Bolt', 10, 50));

        self::assertCount(3, $invoice->productLines());
        self::assertSame(StatusEnum::Draft, $invoice->status());
    }

    #[DataProvider('nonDraftStatusProvider')]
    public function testAddProductLineFailsWhenNotInDraftStatus(StatusEnum $status): void
    {
        $invoice = $this->createInvoiceWithStatus($status);

        $this->expectException(InvoiceMustBeInDraftStatusToAddProductLinesException::class);

        $invoice->addProductLine($this->createProductLine());
    }

    public function testSendTransitionsFromDraftToSending(): void
    {
        $invoice = $this->createDraftInvoice();
        $invoice->addProductLine($this->createProductLine());

        $invoice->send();

        self::assertSame(StatusEnum::Sending, $invoice->status());
    }

    public function testSendDoesNotChangeCreatedAt(): void
    {
        $invoice = $this->createDraftInvoice();
        $invoice->addProductLine($this->createProductLine());
        $createdAt = $invoice->createdAt();

        $invoice->send();

        self::assertEquals($createdAt, $invoice->createdAt());
    }

    public function testSendFailsWhenNoProductLines(): void
    {
        $invoice = $this->createDraftInvoice();

        $this->expectException(InvoiceMustContainProductLinesToBeSentException::class);

        $invoice->send();
    }

    #[DataProvider('nonDraftStatusProvider')]
    public function testSendFailsWhenNotInDraftStatus(StatusEnum $status): void
    {
        $invoice = $this->createInvoiceWithStatus($status, [$this->createProductLine()]);

        $this->expectException(InvoiceMustBeInDraftStatusToBeSentException::class);

        $invoice->send();
    }

    public function testMarkAsDeliveredTransitionsFromSendingToSentToClient(): void
    {
        $invoice = $this->createDraftInvoice();
        $invoice->addProductLine($this->createProductLine());
        $invoice->send();

        $invoice->markAsDelivered();

        self::assertSame(StatusEnum::SentToClient, $invoice->status());
    }

    #[DataProvider('nonSendingStatusProvider')]
    public function testMarkAsDeliveredFailsWhenNotInSendingStatus(StatusEnum $status): void
    {
        $invoice = $this->createInvoiceWithStatus($status);

        $this->expectException(InvoiceMustBeInSendingStatusToBeMarkedAsDeliveredException::class);

        $invoice->markAsDelivered();
    }

    public function testFullLifecycleDraftToSendingToSentToClient(): void
    {
        $invoice = $this->createDraftInvoice();
        self::assertSame(StatusEnum::Draft, $invoice->status());

        $invoice->addProductLine($this->createProductLine());
        $invoice->send();
        self::assertSame(StatusEnum::Sending, $invoice->status());

        $invoice->markAsDelivered();
        self::assertSame(StatusEnum::SentToClient, $invoice->status());
    }

    public function testCannotSendTwice(): void
    {
        $invoice = $this->createDraftInvoice();
        $invoice->addProductLine($this->createProductLine());
        $invoice->send();

        $this->expectException(InvoiceMustBeInDraftStatusToBeSentException::class);

        $invoice->send();
    }

    public function testCannotMarkAsDeliveredTwice(): void
    {
        $invoice = $this->createDraftInvoice();
        $invoice->addProductLine($this->createProductLine());
        $invoice->send();
        $invoice->markAsDelivered();

        $this->expectException(InvoiceMustBeInSendingStatusToBeMarkedAsDeliveredException::class);

        $invoice->markAsDelivered();
    }

    public function testCannotAddProductLineAfterSending(): void
    {
        $invoice = $this->createDraftInvoice();
        $invoice->addProductLine($this->createProductLine());
        $invoice->send();

        $this->expectException(InvoiceMustBeInDraftStatusToAddProductLinesException::class);

        $invoice->addProductLine($this->createProductLine());
    }

    public function testCannotAddProductLineAfterDelivered(): void
    {
        $invoice = $this->createDraftInvoice();
        $invoice->addProductLine($this->createProductLine());
        $invoice->send();
        $invoice->markAsDelivered();

        $this->expectException(InvoiceMustBeInDraftStatusToAddProductLinesException::class);

        $invoice->addProductLine($this->createProductLine());
    }

    public static function nonDraftStatusProvider(): array
    {
        return [
            'sending' => [StatusEnum::Sending],
            'sent to client' => [StatusEnum::SentToClient],
        ];
    }

    public static function nonSendingStatusProvider(): array
    {
        return [
            'draft' => [StatusEnum::Draft],
            'sent to client' => [StatusEnum::SentToClient],
        ];
    }

    public static function statusProvider(): array
    {
        return [
            'draft' => [StatusEnum::Draft],
            'sending' => [StatusEnum::Sending],
            'sent to client' => [StatusEnum::SentToClient],
        ];
    }

    private function createDraftInvoice(): Invoice
    {
        return Invoice::create(
            InvoiceId::generate(),
            CustomerName::create('John Doe'),
            CustomerEmail::create('john@example.com'),
        );
    }

    private function createProductLine(string $name = 'Widget', int $price = 100, int $quantity = 2): InvoiceProductLine
    {
        return InvoiceProductLine::create(
            ProductLineId::generate(),
            ProductLineName::create($name),
            Price::create($price),
            Quantity::create($quantity),
        );
    }

    private function createInvoiceWithStatus(StatusEnum $status, array $productLines = []): Invoice
    {
        return Invoice::reconstitute(
            InvoiceId::generate(),
            CustomerName::create('John Doe'),
            CustomerEmail::create('john@example.com'),
            $status,
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
            $productLines,
        );
    }
}
