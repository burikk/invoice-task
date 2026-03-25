<?php

declare(strict_types=1);

namespace Modules\Invoices\Infrastructure\QueryBuilder;

use Illuminate\Support\Facades\DB;
use Modules\Invoices\Domain\Entities\Invoice;
use Modules\Invoices\Domain\Entities\InvoiceProductLine;
use Modules\Invoices\Domain\Enums\StatusEnum;
use Modules\Invoices\Domain\Repository\InvoiceWriteRepositoryInterface;
use Modules\Invoices\Domain\ValueObjects\CustomerEmail;
use Modules\Invoices\Domain\ValueObjects\CustomerName;
use Modules\Invoices\Domain\ValueObjects\InvoiceId;
use Modules\Invoices\Domain\ValueObjects\Price;
use Modules\Invoices\Domain\ValueObjects\ProductLineId;
use Modules\Invoices\Domain\ValueObjects\ProductLineName;
use Modules\Invoices\Domain\ValueObjects\Quantity;

final readonly class QueryBuilderInvoiceWriteRepository implements InvoiceWriteRepositoryInterface
{
    public function save(Invoice $invoice): void
    {
        DB::transaction(function () use ($invoice) {
            $invoiceId = $invoice->invoiceId()->toString();

            $exists = DB::table('invoices')->where('id', $invoiceId)->exists();

            if ($exists) {
                DB::table('invoices')
                    ->where('id', $invoiceId)
                    ->update([
                        'customer_name' => $invoice->customerName()->value,
                        'customer_email' => $invoice->customerEmail()->value,
                        'status' => $invoice->status()->value,
                        'updated_at' => $invoice->updatedAt(),
                    ]);
            } else {
                DB::table('invoices')->insert([
                    'id' => $invoiceId,
                    'customer_name' => $invoice->customerName()->value,
                    'customer_email' => $invoice->customerEmail()->value,
                    'status' => $invoice->status()->value,
                    'created_at' => $invoice->createdAt(),
                    'updated_at' => $invoice->updatedAt(),
                ]);
            }

            DB::table('invoice_product_lines')
                ->where('invoice_id', $invoiceId)
                ->delete();

            $productLineRows = [];
            foreach ($invoice->productLines() as $productLine) {
                $productLineRows[] = [
                    'id' => $productLine->productLineId()->toString(),
                    'invoice_id' => $invoiceId,
                    'name' => $productLine->productName()->value,
                    'quantity' => $productLine->quantity()->value,
                    'price' => $productLine->price()->value,
                    'created_at' => $productLine->createdAt(),
                    'updated_at' => $productLine->updatedAt(),
                ];
            }

            if ($productLineRows !== []) {
                DB::table('invoice_product_lines')->insert($productLineRows);
            }
        });
    }

    public function findById(InvoiceId $id): ?Invoice
    {
        $invoiceRow = DB::table('invoices')->where('id', $id->toString())->first();

        if ($invoiceRow === null) {
            return null;
        }

        $productLineRows = DB::table('invoice_product_lines')
            ->where('invoice_id', $invoiceRow->id)
            ->get();

        $productLines = $productLineRows->map(
            fn(object $line) => InvoiceProductLine::reconstitute(
                productLineId: ProductLineId::fromString($line->id),
                productName: ProductLineName::create($line->name),
                price: Price::create((int) $line->price),
                quantity: Quantity::create((int) $line->quantity),
                createdAt: new \DateTimeImmutable($line->created_at),
                updatedAt: new \DateTimeImmutable($line->updated_at),
            )
        )->all();

        return Invoice::reconstitute(
            id: InvoiceId::fromString($invoiceRow->id),
            customerName: CustomerName::create($invoiceRow->customer_name),
            customerEmail: CustomerEmail::create($invoiceRow->customer_email),
            status: StatusEnum::from($invoiceRow->status),
            createdAt: new \DateTimeImmutable($invoiceRow->created_at),
            updatedAt: new \DateTimeImmutable($invoiceRow->updated_at),
            productLines: $productLines,
        );
    }
}
