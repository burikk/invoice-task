<?php

declare(strict_types=1);

namespace Modules\Invoices\Infrastructure\QueryBuilder;

use Illuminate\Support\Facades\DB;
use Modules\Invoices\Domain\Repository\InvoiceReadRepositoryInterface;
use Modules\Invoices\Presentation\ViewModels\InvoiceProductLineView;
use Modules\Invoices\Presentation\ViewModels\InvoiceView;

final readonly class QueryBuilderInvoiceReadRepository implements InvoiceReadRepositoryInterface
{
    public function findById(string $id): ?InvoiceView
    {
        $invoice = DB::table('invoices')
            ->where('id', $id)
            ->first();

        if ($invoice === null) {
            return null;
        }

        $productLines = DB::table('invoice_product_lines')
            ->where('invoice_id', $id)
            ->get()
            ->map(fn(object $row) => new InvoiceProductLineView(
                id: $row->id,
                productName: $row->name,
                quantity: (int) $row->quantity,
                unitPrice: (int) $row->price,
            ))
            ->all();

        return new InvoiceView(
            id: $invoice->id,
            status: $invoice->status,
            customerName: $invoice->customer_name,
            customerEmail: $invoice->customer_email,
            productLines: $productLines,
        );
    }
}
