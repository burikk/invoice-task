<?php

declare(strict_types=1);

namespace Modules\Shared\Domain\Exceptions;

final class InvalidUuidFormatException extends DomainException
{
    public function __construct()
    {
        parent::__construct('The provided UUID is not in a valid format.');
    }
}
