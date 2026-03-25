<?php

declare(strict_types=1);

namespace Tests\Feature\Invoice\Http;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Invoices\Domain\Enums\StatusEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class CreateInvoiceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testCreatesInvoiceWithProductLines(): void
    {
        $payload = [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'invoice_product_lines' => [
                ['name' => 'Widget', 'quantity' => 3, 'price' => 100],
                ['name' => 'Gadget', 'quantity' => 2, 'price' => 250],
            ],
        ];

        $this->postJson('/api/invoices', $payload)
            ->assertCreated();

        $this->assertDatabaseCount('invoices', 1);
        $this->assertDatabaseHas('invoices', [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'status' => StatusEnum::Draft->value,
        ]);

        $this->assertDatabaseCount('invoice_product_lines', 2);
        $this->assertDatabaseHas('invoice_product_lines', [
            'name' => 'Widget',
            'quantity' => 3,
            'price' => 100,
        ]);
        $this->assertDatabaseHas('invoice_product_lines', [
            'name' => 'Gadget',
            'quantity' => 2,
            'price' => 250,
        ]);
    }

    public function testCreatesInvoiceWithEmptyProductLines(): void
    {
        $payload = [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'invoice_product_lines' => [],
        ];

        $this->postJson('/api/invoices', $payload)
            ->assertCreated();

        $this->assertDatabaseCount('invoices', 1);
        $this->assertDatabaseHas('invoices', [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'status' => StatusEnum::Draft->value,
        ]);

        $this->assertDatabaseCount('invoice_product_lines', 0);
    }

    public function testCreatedInvoiceAlwaysHasDraftStatus(): void
    {
        $payload = [
            'customer_name' => 'Test User',
            'customer_email' => 'test@example.com',
            'invoice_product_lines' => [],
        ];

        $this->postJson('/api/invoices', $payload)
            ->assertCreated();

        $invoice = DB::table('invoices')->first();

        self::assertNotNull($invoice);
        self::assertSame(StatusEnum::Draft->value, $invoice->status);
    }

    #[DataProvider('missingFieldProvider')]
    public function testReturnsValidationErrorWhenRequiredFieldMissing(array $payload): void
    {
        $this->postJson('/api/invoices', $payload)
            ->assertUnprocessable();

        $this->assertDatabaseCount('invoices', 0);
    }

    public static function missingFieldProvider(): array
    {
        return [
            'missing customer_name' => [[
                'customer_email' => 'test@example.com',
                'invoice_product_lines' => [],
            ]],
            'missing customer_email' => [[
                'customer_name' => 'Test',
                'invoice_product_lines' => [],
            ]],
            'missing invoice_product_lines' => [[
                'customer_name' => 'Test',
                'customer_email' => 'test@example.com',
            ]],
            'empty payload' => [[]],
        ];
    }

    #[DataProvider('invalidFieldProvider')]
    public function testReturnsValidationErrorWhenFieldIsInvalid(array $payload): void
    {
        $this->postJson('/api/invoices', $payload)
            ->assertUnprocessable();

        $this->assertDatabaseCount('invoices', 0);
    }

    public static function invalidFieldProvider(): array
    {
        return [
            'invalid email format' => [[
                'customer_name' => 'Test',
                'customer_email' => 'not-an-email',
                'invoice_product_lines' => [],
            ]],
            'product line missing name' => [[
                'customer_name' => 'Test',
                'customer_email' => 'test@example.com',
                'invoice_product_lines' => [
                    ['quantity' => 1, 'price' => 100],
                ],
            ]],
            'product line missing quantity' => [[
                'customer_name' => 'Test',
                'customer_email' => 'test@example.com',
                'invoice_product_lines' => [
                    ['name' => 'Widget', 'price' => 100],
                ],
            ]],
            'product line missing price' => [[
                'customer_name' => 'Test',
                'customer_email' => 'test@example.com',
                'invoice_product_lines' => [
                    ['name' => 'Widget', 'quantity' => 1],
                ],
            ]],
            'product line zero quantity' => [[
                'customer_name' => 'Test',
                'customer_email' => 'test@example.com',
                'invoice_product_lines' => [
                    ['name' => 'Widget', 'quantity' => 0, 'price' => 100],
                ],
            ]],
            'product line negative quantity' => [[
                'customer_name' => 'Test',
                'customer_email' => 'test@example.com',
                'invoice_product_lines' => [
                    ['name' => 'Widget', 'quantity' => -1, 'price' => 100],
                ],
            ]],
            'product line zero price' => [[
                'customer_name' => 'Test',
                'customer_email' => 'test@example.com',
                'invoice_product_lines' => [
                    ['name' => 'Widget', 'quantity' => 1, 'price' => 0],
                ],
            ]],
            'product line negative price' => [[
                'customer_name' => 'Test',
                'customer_email' => 'test@example.com',
                'invoice_product_lines' => [
                    ['name' => 'Widget', 'quantity' => 1, 'price' => -5],
                ],
            ]],
        ];
    }
}

