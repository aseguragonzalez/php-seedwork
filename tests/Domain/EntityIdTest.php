<?php

declare(strict_types=1);

namespace Tests\Domain;

use PHPUnit\Framework\TestCase;
use SeedWork\Domain\Exceptions\ValueException;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccountId;

final class EntityIdTest extends TestCase
{
    public function testEquals(): void
    {
        $id1 = BankAccountId::create();
        $id2 = BankAccountId::create();
        $id3 = BankAccountId::fromString($id1->value);

        $this->assertFalse($id1->equals($id2));
        $this->assertTrue($id1->equals($id3));
    }

    public function testToString(): void
    {
        $id = BankAccountId::create();

        $this->assertSame($id->value, (string)$id);
    }

    public function testValidationFailsWhenEmpty(): void
    {
        $this->expectException(ValueException::class);
        $this->expectExceptionMessage('Bank account id cannot be empty');

        BankAccountId::fromString('');
    }

    public function testValidationFailsWhenDoesNotStartWithAcc(): void
    {
        $this->expectException(ValueException::class);
        $this->expectExceptionMessage('Bank account id must start with "acc-"');

        BankAccountId::fromString('1234567890');
    }
}
