<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Invoices\Domain\ValueObjects;

use Modules\Invoices\Domain\Exceptions\CustomerNameCannotBeEmptyException;
use Modules\Invoices\Domain\ValueObjects\CustomerName;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CustomerNameTest extends TestCase
{
    #[DataProvider('validNameProvider')]
    public function testCreatesCustomerNameWithValidName(string $name): void
    {
        $customerName = CustomerName::create($name);

        self::assertSame($name, $customerName->value);
    }

    public function testThrowsExceptionWhenNameIsEmpty(): void
    {
        $this->expectException(CustomerNameCannotBeEmptyException::class);

        CustomerName::create('');
    }

    public static function validNameProvider(): array
    {
        return [
            'simple name' => ['John'],
            'full name' => ['John Doe'],
            'name with hyphen' => ['Mary-Jane Watson'],
            'name with apostrophe' => ["O'Brien"],
            'single character' => ['A'],
        ];
    }
}
