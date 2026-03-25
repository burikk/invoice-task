<?php

declare(strict_types=1);

namespace Modules\Invoices\Presentation\Http\Controllers;

use Modules\Invoices\Application\UseCase\Commands\SendInvoiceCommand;
use Modules\Shared\Application\Bus\Command\CommandBusInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class SendInvoiceController
{
    public function __construct(private CommandBusInterface $commandBus)
    {
    }

    public function __invoke(string $id): JsonResponse
    {
        $command = new SendInvoiceCommand($id);

        $this->commandBus->execute($command);

        return new JsonResponse(status: Response::HTTP_OK);
    }
}
