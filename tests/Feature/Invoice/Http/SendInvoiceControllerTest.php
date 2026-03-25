<?php

declare(strict_types=1);

namespace Tests\Feature\Invoice\Http;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Invoices\Domain\Enums\StatusEnum;
use Modules\Notifications\Api\Dtos\NotifyData;
use Modules\Notifications\Api\NotificationFacadeInterface;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class SendInvoiceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testSendsInvoiceSuccessfully(): void
    {
        $invoiceId = Uuid::uuid4()->toString();
        $lineId = Uuid::uuid4()->toString();
        $createdAt = '2025-01-01 10:00:00';

        DB::table('invoices')->insert([
            'id' => $invoiceId,
            'customer_name' => 'Alice Smith',
            'customer_email' => 'alice@example.com',
            'status' => StatusEnum::Draft->value,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        DB::table('invoice_product_lines')->insert([
            'id' => $lineId,
            'invoice_id' => $invoiceId,
            'name' => 'Widget',
            'price' => 100,
            'quantity' => 3,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        $this->mock(NotificationFacadeInterface::class, function (MockInterface $mock) use ($invoiceId) {
            $mock->shouldReceive('notify')
                ->once()
                ->withArgs(function (NotifyData $data) use ($invoiceId) {
                    return $data->resourceId->toString() === $invoiceId
                        && $data->toEmail === 'alice@example.com'
                        && str_contains($data->subject, 'Alice Smith');
                });
        });

        $this->postJson("/api/invoices/{$invoiceId}/send")
            ->assertOk();

        $invoice = DB::table('invoices')->where('id', $invoiceId)->first();

        self::assertSame(StatusEnum::Sending->value, $invoice->status);
        self::assertSame($createdAt, $invoice->created_at);
        self::assertGreaterThan($createdAt, $invoice->updated_at);

        $this->assertDatabaseHas('invoice_product_lines', [
            'id' => $lineId,
            'invoice_id' => $invoiceId,
            'name' => 'Widget',
            'price' => 100,
            'quantity' => 3,
        ]);
    }

    public function testCannotSendSameInvoiceTwice(): void
    {
        $invoiceId = Uuid::uuid4()->toString();
        $this->seedInvoiceWithLine($invoiceId);

        $this->mock(NotificationFacadeInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('notify')->once();
        });

        $this->postJson("/api/invoices/{$invoiceId}/send")->assertOk();

        $this->postJson("/api/invoices/{$invoiceId}/send")
            ->assertUnprocessable()
            ->assertJson(['error' => 'Invoice can only be sent if it is in draft status.']);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoiceId,
            'status' => StatusEnum::Sending->value,
        ]);
    }

    #[DataProvider('domainViolationProvider')]
    public function testRejectsSendWhenDomainRulesAreViolated(
        StatusEnum $status,
        bool $withProductLine,
        string $expectedError,
    ): void {
        $invoiceId = Uuid::uuid4()->toString();

        DB::table('invoices')->insert([
            'id' => $invoiceId,
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'status' => $status->value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($withProductLine) {
            DB::table('invoice_product_lines')->insert([
                'id' => Uuid::uuid4()->toString(),
                'invoice_id' => $invoiceId,
                'name' => 'Widget',
                'price' => 100,
                'quantity' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->postJson("/api/invoices/{$invoiceId}/send")
            ->assertUnprocessable()
            ->assertJson(['error' => $expectedError]);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoiceId,
            'status' => $status->value,
        ]);
    }

    public static function domainViolationProvider(): array
    {
        return [
            'draft without product lines' => [
                StatusEnum::Draft,
                false,
                'Invoice must contain at least one product line to be sent.',
            ],
            'sending status with product lines' => [
                StatusEnum::Sending,
                true,
                'Invoice can only be sent if it is in draft status.',
            ],
            'sent-to-client status with product lines' => [
                StatusEnum::SentToClient,
                true,
                'Invoice can only be sent if it is in draft status.',
            ],
        ];
    }

    public function testReturns404WhenInvoiceDoesNotExist(): void
    {
        $this->postJson('/api/invoices/' . Uuid::uuid4()->toString() . '/send')
            ->assertNotFound()
            ->assertJson(['error' => 'Invoice not found.']);

        $this->postJson('/api/invoices/not-a-uuid/send')
            ->assertNotFound();

        $this->assertDatabaseCount('invoices', 0);
    }

    private function seedInvoiceWithLine(string $invoiceId): void
    {
        DB::table('invoices')->insert([
            'id' => $invoiceId,
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'status' => StatusEnum::Draft->value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('invoice_product_lines')->insert([
            'id' => Uuid::uuid4()->toString(),
            'invoice_id' => $invoiceId,
            'name' => 'Widget',
            'price' => 100,
            'quantity' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

