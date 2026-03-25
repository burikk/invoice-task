<?php

declare(strict_types=1);

namespace Modules\Shared\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Invoices\Application\UseCase\Commands\CreateInvoiceCommand;
use Modules\Invoices\Application\UseCase\Commands\SendInvoiceCommand;
use Modules\Invoices\Application\UseCase\Handlers\CreateInvoiceHandler;
use Modules\Invoices\Application\UseCase\Handlers\SendInvoiceHandler;
use Modules\Invoices\Application\UseCase\Handlers\ViewInvoiceHandler;
use Modules\Invoices\Application\UseCase\Queries\ViewInvoiceQuery;
use Modules\Shared\Application\Bus\Command\CommandBusInterface;
use Modules\Shared\Application\Bus\Query\QueryBusInterface;
use Modules\Shared\Infrastructure\Bus\CommandBus\CommandBus;
use Modules\Shared\Infrastructure\Bus\QueryBus\QueryBus;

final class BusServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CommandBusInterface::class, CommandBus::class);
        $this->app->singleton(QueryBusInterface::class, QueryBus::class);
    }

    public function boot(): void
    {
        $this->app->resolving(CommandBusInterface::class, function (CommandBusInterface $commandBus): void {
            $commandBus->register([
                CreateInvoiceCommand::class => CreateInvoiceHandler::class,
                SendInvoiceCommand::class => SendInvoiceHandler::class,
            ]);
        });

        $this->app->resolving(QueryBusInterface::class, function (QueryBusInterface $queryBus): void {
            $queryBus->register([
                ViewInvoiceQuery::class => ViewInvoiceHandler::class,
            ]);
        });
    }
}
