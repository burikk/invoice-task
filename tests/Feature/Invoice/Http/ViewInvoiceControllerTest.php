<?php

declare(strict_types=1);

namespace Tests\Feature\Invoice\Http;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Invoices\Domain\Enums\StatusEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class ViewInvoiceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testReturnsInvoiceWithProductLines(): void
    {
        $invoiceId = Uuid::uuid4()->toString();
        $lineId1 = Uuid::uuid4()->toString();
        $lineId2 = Uuid::uuid4()->toString();

        DB::table('invoices')->insert([
            'id' => $invoiceId,
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'status' => StatusEnum::Draft->value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('invoice_product_lines')->insert([
            [
                'id' => $lineId1,
                'invoice_id' => $invoiceId,
                'name' => 'Widget',
                'price' => 100,
                'quantity' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $lineId2,
                'invoice_id' => $invoiceId,
                'name' => 'Gadget',
                'price' => 250,
                'quantity' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->getJson("/api/invoices/{$invoiceId}")
            ->assertOk()
            ->assertExactJson([
                'id' => $invoiceId,
                'status' => 'draft',
                'customerName' => 'John Doe',
                'customerEmail' => 'john@example.com',
                'totalPrice' => 800,
                'productLines' => [
                    [
                        'id' => $lineId1,
                        'productName' => 'Widget',
                        'quantity' => 3,
                        'unitPrice' => 100,
                        'totalUnitPrice' => 300,
                    ],
                    [
                        'id' => $lineId2,
                        'productName' => 'Gadget',
                        'quantity' => 2,
                        'unitPrice' => 250,
                        'totalUnitPrice' => 500,
                    ],
                ],
            ]);
    }

    public function testReturnsInvoiceWithEmptyProductLines(): void
    {
        $invoiceId = Uuid::uuid4()->toString();

        DB::table('invoices')->insert([
            'id' => $invoiceId,
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'status' => StatusEnum::Draft->value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson("/api/invoices/{$invoiceId}")
            ->assertOk()
            ->assertExactJson([
                'id' => $invoiceId,
                'status' => 'draft',
                'customerName' => 'Jane Doe',
                'customerEmail' => 'jane@example.com',
                'totalPrice' => 0,
                'productLines' => [],
            ]);
    }

    #[DataProvider('statusProvider')]
    public function testReturnsCorrectStatusForEachState(StatusEnum $status, string $expectedStatus): void
    {
        $invoiceId = Uuid::uuid4()->toString();

        DB::table('invoices')->insert([
            'id' => $invoiceId,
            'customer_name' => 'Test',
            'customer_email' => 'test@example.com',
            'status' => $status->value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson("/api/invoices/{$invoiceId}")
            ->assertOk()
            ->assertJsonFragment(['status' => $expectedStatus]);
    }

    public static function statusProvider(): array
    {
        return [
            'draft' => [StatusEnum::Draft, 'draft'],
            'sending' => [StatusEnum::Sending, 'sending'],
            'sent to client' => [StatusEnum::SentToClient, 'sent-to-client'],
        ];
    }

    public function testReturns404WhenInvoiceDoesNotExist(): void
    {
        $nonExistentId = Uuid::uuid4()->toString();

        $this->getJson("/api/invoices/{$nonExistentId}")
            ->assertNotFound()
            ->assertJson(['error' => 'Invoice not found.']);
    }

    public function testReturns404ForInvalidUuidFormat(): void
    {
        $this->getJson('/api/invoices/not-a-uuid')
            ->assertNotFound();
    }

    public function testDoesNotReturnProductLinesFromAnotherInvoice(): void
    {
        $invoiceId1 = Uuid::uuid4()->toString();
        $invoiceId2 = Uuid::uuid4()->toString();

        DB::table('invoices')->insert([
            [
                'id' => $invoiceId1,
                'customer_name' => 'Owner',
                'customer_email' => 'owner@example.com',
                'status' => StatusEnum::Draft->value,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $invoiceId2,
                'customer_name' => 'Other',
                'customer_email' => 'other@example.com',
                'status' => StatusEnum::Draft->value,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('invoice_product_lines')->insert([
            'id' => Uuid::uuid4()->toString(),
            'invoice_id' => $invoiceId2,
            'name' => 'Other Product',
            'price' => 999,
            'quantity' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson("/api/invoices/{$invoiceId1}")
            ->assertOk()
            ->assertJsonFragment(['productLines' => []])
            ->assertJsonMissing(['productName' => 'Other Product']);
    }
}
