<?php

declare(strict_types=1);

namespace Modules\Shared\Domain\ValueObjects;

use Modules\Shared\Domain\Exceptions\InvalidUuidFormatException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

trait UuidTrait
{
    private function __construct(private readonly string $uuid)
    {
        if (!Uuid::isValid($uuid)) {
            throw new InvalidUuidFormatException();
        }
    }

    public static function generate(): static
    {
        return new static(Uuid::uuid4()->toString());
    }

    public static function fromString(string $uuid): static
    {
        return new static($uuid);
    }

    public static function fromUuid(UuidInterface $uuid): static
    {
        return new static($uuid->toString());
    }

    public function toString(): string
    {
        return $this->uuid;
    }

    public function equals(self $other): bool
    {
        return $this->uuid === $other->uuid;
    }

    public function toUuid(): UuidInterface
    {
        return Uuid::fromString($this->uuid);
    }
}
