<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\UseCase\Handlers;

use Modules\Invoices\Application\UseCase\Commands\CreateInvoiceCommand;
use Modules\Invoices\Domain\Entities\Invoice;
use Modules\Invoices\Domain\Entities\InvoiceProductLine;
use Modules\Invoices\Domain\Repository\InvoiceWriteRepositoryInterface;
use Modules\Invoices\Domain\ValueObjects\CustomerEmail;
use Modules\Invoices\Domain\ValueObjects\CustomerName;
use Modules\Invoices\Domain\ValueObjects\InvoiceId;
use Modules\Invoices\Domain\ValueObjects\Price;
use Modules\Invoices\Domain\ValueObjects\ProductLineId;
use Modules\Invoices\Domain\ValueObjects\ProductLineName;
use Modules\Invoices\Domain\ValueObjects\Quantity;

final readonly class CreateInvoiceHandler
{
    public function __construct(private InvoiceWriteRepositoryInterface $repository)
    {
    }

    public function handle(CreateInvoiceCommand $command): void
    {
        $invoice = Invoice::create(
            InvoiceId::generate(),
            CustomerName::create($command->customerName),
            CustomerEmail::create($command->customerEmail),
        );

        foreach ($command->productLines as $productLine) {
            $productLine = InvoiceProductLine::create(
                ProductLineId::generate(),
                ProductLineName::create($productLine->name),
                Price::create($productLine->price),
                Quantity::create($productLine->quantity),
            );

            $invoice->addProductLine($productLine);
        }

        $this->repository->save($invoice);
    }
}
