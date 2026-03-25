<?php

declare(strict_types=1);

namespace Modules\Invoices\Presentation\Http\Controllers;

use Modules\Invoices\Application\UseCase\Queries\ViewInvoiceQuery;
use Modules\Shared\Application\Bus\Query\QueryBusInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class ViewInvoiceController
{
    public function __construct(private QueryBusInterface $queryBus)
    {
    }

    public function __invoke(string $id): JsonResponse
    {
        $query = new ViewInvoiceQuery($id);

        $view = $this->queryBus->execute($query);

        return new JsonResponse($view, Response::HTTP_OK);
    }
}
