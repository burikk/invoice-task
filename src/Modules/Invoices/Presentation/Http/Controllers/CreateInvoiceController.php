<?php

declare(strict_types=1);

namespace Modules\Invoices\Presentation\Http\Controllers;

use Modules\Invoices\Application\UseCase\Commands\CreateInvoiceCommand;
use Modules\Invoices\Application\UseCase\Commands\DTOs\InvoiceProductLineDTO;
use Modules\Invoices\Presentation\Http\Requests\CreateInvoiceRequest;
use Modules\Shared\Application\Bus\Command\CommandBusInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class CreateInvoiceController
{
    public function __construct(private CommandBusInterface $commandBus)
    {
    }

    public function __invoke(CreateInvoiceRequest $request): JsonResponse
    {
        $productLines = array_map(
            fn(array $line) => new InvoiceProductLineDTO(
                name: $line['name'],
                quantity: (int) $line['quantity'],
                price: (int) $line['price'],
            ),
            $request->input('invoice_product_lines', []),
        );

        $command = new CreateInvoiceCommand(
            customerName: $request->input('customer_name'),
            customerEmail: $request->input('customer_email'),
            productLines: $productLines,
        );

        $this->commandBus->execute($command);

        return new JsonResponse(status: Response::HTTP_CREATED);
    }
}
