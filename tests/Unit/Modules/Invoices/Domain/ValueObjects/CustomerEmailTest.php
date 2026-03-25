<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Invoices\Domain\ValueObjects;

use Modules\Invoices\Domain\Exceptions\CustomerEmailCannotBeEmptyException;
use Modules\Invoices\Domain\Exceptions\CustomerEmailMustBeValidException;
use Modules\Invoices\Domain\ValueObjects\CustomerEmail;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CustomerEmailTest extends TestCase
{
    #[DataProvider('validEmailProvider')]
    public function testCreatesCustomerEmailWithValidEmail(string $email): void
    {
        $customerEmail = CustomerEmail::create($email);

        self::assertSame($email, $customerEmail->value);
    }

    public function testThrowsExceptionWhenEmailIsEmpty(): void
    {
        $this->expectException(CustomerEmailCannotBeEmptyException::class);

        CustomerEmail::create('');
    }

    #[DataProvider('invalidEmailProvider')]
    public function testThrowsExceptionWhenEmailIsInvalid(string $email): void
    {
        $this->expectException(CustomerEmailMustBeValidException::class);

        CustomerEmail::create($email);
    }

    public static function validEmailProvider(): array
    {
        return [
            'simple email' => ['john@example.com'],
            'subdomain email' => ['user@mail.example.com'],
            'plus addressing' => ['user+tag@example.com'],
            'numeric local part' => ['123@example.com'],
            'dotted local part' => ['john.doe@example.com'],
        ];
    }

    public static function invalidEmailProvider(): array
    {
        return [
            'missing at sign' => ['john.example.com'],
            'missing domain' => ['john@'],
            'missing local part' => ['@example.com'],
            'contains spaces' => ['john doe@example.com'],
            'plain string' => ['not-an-email'],
            'double at sign' => ['john@@example.com'],
        ];
    }
}
