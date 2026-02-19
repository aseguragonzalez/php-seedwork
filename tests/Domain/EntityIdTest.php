<?php

declare(strict_types=1);

namespace Tests\Domain;

use PHPUnit\Framework\TestCase;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccountId;
use Tests\Fixtures\BankAccount\Domain\Entities\TransactionId;

final class EntityIdTest extends TestCase
{
    public function testBankAccountIdFromString(): void
    {
        $id = BankAccountId::fromString('acc-001');

        $this->assertSame('acc-001', $id->value);
        $this->assertSame('acc-001', (string) $id);
    }

    public function testBankAccountIdEquals(): void
    {
        $a = BankAccountId::fromString('acc-001');
        $b = BankAccountId::fromString('acc-001');
        $c = BankAccountId::fromString('acc-002');

        $this->assertTrue($a->equals($b));
        $this->assertTrue($b->equals($a));
        $this->assertFalse($a->equals($c));
        $this->assertFalse($c->equals($a));
    }

    public function testTransactionIdFromString(): void
    {
        $id = TransactionId::fromString('txn-123');

        $this->assertSame('txn-123', $id->value);
        $this->assertSame('txn-123', (string) $id);
    }

    public function testTransactionIdGenerate(): void
    {
        $id = TransactionId::generate();

        $this->assertStringStartsWith('txn-', $id->value);
        $this->assertNotEquals(TransactionId::generate()->value, $id->value);
    }

    public function testTransactionIdEquals(): void
    {
        $a = TransactionId::fromString('txn-a');
        $b = TransactionId::fromString('txn-a');
        $c = TransactionId::fromString('txn-b');

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }
}
