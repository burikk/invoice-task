<?php

declare(strict_types=1);

namespace Modules\Invoices\Infrastructure\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Modules\Invoices\Application\UseCase\Listeners\InvoiceDeliveredListener;
use Modules\Invoices\Domain\Repository\InvoiceReadRepositoryInterface;
use Modules\Invoices\Domain\Repository\InvoiceWriteRepositoryInterface;
use Modules\Invoices\Infrastructure\QueryBuilder\QueryBuilderInvoiceReadRepository;
use Modules\Invoices\Infrastructure\QueryBuilder\QueryBuilderInvoiceWriteRepository;
use Modules\Notifications\Api\Events\WebhookDeliveredEvent;

final class InvoiceServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(WebhookDeliveredEvent::class, InvoiceDeliveredListener::class);
    }

    public function register(): void
    {
        $this->app->bind(InvoiceWriteRepositoryInterface::class, QueryBuilderInvoiceWriteRepository::class);
        $this->app->bind(InvoiceReadRepositoryInterface::class, QueryBuilderInvoiceReadRepository::class);
    }
}
