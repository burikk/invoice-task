<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\UseCase\Handlers;

use Modules\Invoices\Application\Exceptions\InvoiceNotFoundException;
use Modules\Invoices\Application\UseCase\Queries\ViewInvoiceQuery;
use Modules\Invoices\Domain\Repository\InvoiceReadRepositoryInterface;
use Modules\Invoices\Presentation\ViewModels\InvoiceView;

final readonly class ViewInvoiceHandler
{
    public function __construct(private InvoiceReadRepositoryInterface $repository)
    {
    }

    public function handle(ViewInvoiceQuery $query): InvoiceView
    {
        $invoice = $this->repository->findById($query->id);

        if ($invoice === null) {
            throw new InvoiceNotFoundException();
        }

        return $invoice;
    }
}