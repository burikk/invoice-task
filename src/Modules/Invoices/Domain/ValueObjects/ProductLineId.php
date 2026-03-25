<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\ValueObjects;

use Modules\Shared\Domain\ValueObjects\UuidTrait;

final readonly class ProductLineId
{
    use UuidTrait;
}
