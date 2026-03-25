<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\UseCase\Listeners;

use Modules\Invoices\Application\Exceptions\InvoiceNotFoundException;
use Modules\Invoices\Domain\Repository\InvoiceWriteRepositoryInterface;
use Modules\Invoices\Domain\ValueObjects\InvoiceId;
use Modules\Notifications\Api\Events\WebhookDeliveredEvent;

final readonly class InvoiceDeliveredListener
{
    public function __construct(private InvoiceWriteRepositoryInterface $repository)
    {
    }

    public function __invoke(WebhookDeliveredEvent $event): void
    {
        $invoice = $this->repository->findById(InvoiceId::fromUuid($event->resourceId));

        if ($invoice === null) {
            throw new InvoiceNotFoundException();
        }

        $invoice->markAsDelivered();

        $this->repository->save($invoice);
    }
}
