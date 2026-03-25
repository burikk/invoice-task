<?php

declare(strict_types=1);

namespace Modules\Notifications\Api\Dtos;

use Ramsey\Uuid\UuidInterface;

final readonly class NotifyData
{
    private function __construct(
        public UuidInterface $resourceId,
        public string $toEmail,
        public string $subject,
        public string $message,
    ) {}

    public static function create(
        UuidInterface $resourceId,
        string $toEmail,
        string $subject,
        string $message,
    ): self
    {
        return new self(
            resourceId: $resourceId,
            toEmail: $toEmail,
            subject: $subject,
            message: $message,
        );
    }
}
