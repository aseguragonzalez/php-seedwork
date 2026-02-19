<?php

declare(strict_types=1);

namespace Tests\Domain;

use PHPUnit\Framework\TestCase;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccount;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccountId;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Currency;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Money;

final class AggregateRootTest extends TestCase
{
    public function testBankAccountIdentityEquality(): void
    {
        $id = BankAccountId::fromString('acc-001');
        $account1 = BankAccount::create($id);
        $account2 = BankAccount::create($id);

        $this->assertTrue($account1->equals($account2));
    }

    public function testBankAccountDifferentIdentitiesNotEqual(): void
    {
        $account1 = BankAccount::create(BankAccountId::fromString('acc-001'));
        $account2 = BankAccount::create(BankAccountId::fromString('acc-002'));

        $this->assertFalse($account1->equals($account2));
    }

    public function testCollectEventsReturnsBufferedEventsAfterWithdraw(): void
    {
        $account = BankAccount::create(BankAccountId::fromString('acc-001'))
            ->deposit(new Money(100, Currency::USD));

        $account = $account->withdraw(new Money(30, Currency::USD));

        $events = $account->collectEvents();

        $this->assertCount(2, $events);
        $this->assertSame('bank_account.money_deposited', $events[0]->type);
        $this->assertSame('bank_account.money_withdrawn', $events[1]->type);
    }
}
