<?php

declare(strict_types=1);

namespace Tests\Domain;

use PHPUnit\Framework\TestCase;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccountId;
use Tests\Fixtures\BankAccount\Domain\Entities\TransactionId;
use Tests\Fixtures\BankAccount\Domain\Events\BankAccountEventId;
use Tests\Fixtures\BankAccount\Domain\Events\MoneyDeposited;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Currency;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Money;

final class DomainEventTest extends TestCase
{
    public function testEquals(): void
    {
        $accountId = BankAccountId::create();
        $transactionId = TransactionId::create();
        $amount = new Money(100, Currency::USD);
        $event1 = MoneyDeposited::create($amount, $accountId, $transactionId);
        $event2 = MoneyDeposited::create($amount, $accountId, $transactionId);
        /** @var BankAccountEventId $id */
        $id = $event1->id;
        $event3 = MoneyDeposited::create($amount, $accountId, $transactionId, $id);

        $this->assertFalse($event1->equals($event2));
        $this->assertTrue($event1->equals($event3));
    }
}
