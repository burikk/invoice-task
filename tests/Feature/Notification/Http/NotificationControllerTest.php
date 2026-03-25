<?php

declare(strict_types=1);

namespace Tests\Feature\Notification\Http;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Invoices\Domain\Enums\StatusEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testDeliveredWebhookTransitionsInvoiceFromSendingToSentToClient(): void
    {
        $invoiceId = Uuid::uuid4()->toString();
        $createdAt = '2025-01-01 10:00:00';

        DB::table('invoices')->insert([
            'id' => $invoiceId,
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'status' => StatusEnum::Sending->value,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        $uri = route('notification.hook', [
            'action' => 'delivered',
            'reference' => $invoiceId,
        ]);

        $this->getJson($uri)->assertOk();

        $invoice = DB::table('invoices')->where('id', $invoiceId)->first();

        self::assertSame(StatusEnum::SentToClient->value, $invoice->status);
        self::assertSame($createdAt, $invoice->created_at);
        self::assertGreaterThan($createdAt, $invoice->updated_at);
    }

    public function testDeliveredWebhookReturns404WhenInvoiceDoesNotExist(): void
    {
        $uri = route('notification.hook', [
            'action' => 'delivered',
            'reference' => Uuid::uuid4()->toString(),
        ]);

        $this->getJson($uri)
            ->assertNotFound()
            ->assertJson(['error' => 'Invoice not found.']);

        $this->assertDatabaseCount('invoices', 0);
    }

    #[DataProvider('nonSendingStatusProvider')]
    public function testDeliveredWebhookRejectsInvoiceNotInSendingStatus(StatusEnum $status): void
    {
        $invoiceId = Uuid::uuid4()->toString();

        DB::table('invoices')->insert([
            'id' => $invoiceId,
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'status' => $status->value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $uri = route('notification.hook', [
            'action' => 'delivered',
            'reference' => $invoiceId,
        ]);

        $this->getJson($uri)
            ->assertUnprocessable()
            ->assertJson(['error' => 'Only invoices in sending status can be marked as delivered.']);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoiceId,
            'status' => $status->value,
        ]);
    }

    public static function nonSendingStatusProvider(): array
    {
        return [
            'draft' => [StatusEnum::Draft],
            'sent to client' => [StatusEnum::SentToClient],
        ];
    }

    public function testUnknownActionReturnsOkAndDoesNotMutateDatabase(): void
    {
        $invoiceId = Uuid::uuid4()->toString();

        DB::table('invoices')->insert([
            'id' => $invoiceId,
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'status' => StatusEnum::Sending->value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $uri = route('notification.hook', [
            'action' => 'unknown',
            'reference' => $invoiceId,
        ]);

        $this->getJson($uri)->assertOk();

        $this->assertDatabaseHas('invoices', [
            'id' => $invoiceId,
            'status' => StatusEnum::Sending->value,
        ]);
    }

    public function testInvalidReferenceFormatReturns404(): void
    {
        $this->getJson('/api/notification/hook/delivered/not-a-uuid')
            ->assertNotFound();

        $this->getJson('/api/notification/hook/delivered/12345')
            ->assertNotFound();

        $this->assertDatabaseCount('invoices', 0);
    }
}
