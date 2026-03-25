<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\UseCase\Handlers;

use Modules\Invoices\Application\Exceptions\InvoiceNotFoundException;
use Modules\Invoices\Application\UseCase\Commands\SendInvoiceCommand;
use Modules\Invoices\Domain\Repository\InvoiceWriteRepositoryInterface;
use Modules\Invoices\Domain\ValueObjects\InvoiceId;
use Modules\Notifications\Api\Dtos\NotifyData;
use Modules\Notifications\Api\NotificationFacadeInterface;

final readonly class SendInvoiceHandler
{
    public function __construct(
        private InvoiceWriteRepositoryInterface $repository,
        private NotificationFacadeInterface $notificationFacade,
    ) {
    }

    public function handle(SendInvoiceCommand $command): void
    {
        $invoice = $this->repository->findById(InvoiceId::fromString($command->id));

        if ($invoice === null) {
            throw new InvoiceNotFoundException();
        }

        $invoice->send();

        $this->repository->save($invoice);

        $this->notificationFacade->notify(NotifyData::create(
            $invoice->invoiceId()->toUuid(),
            $invoice->customerEmail()->value,
            sprintf('Invoice - `<%s>`', $invoice->customerName()->value),
            'Sending you an invoice.',
        ));
    }
}
