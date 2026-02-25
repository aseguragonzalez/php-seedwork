<?php

declare(strict_types=1);

namespace Tests\Application;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\FilterOperator;
use Tests\Fixtures\BankAccount\Application\GetBankAccountStatus\BankAccountFilterCriteria;
use Tests\Fixtures\BankAccount\Application\GetBankAccountStatus\BankAccountProjection;
use Tests\Fixtures\BankAccount\Application\GetBankAccountStatus\BankAccountQueryRepository;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccount;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccountId;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\AccountBalance;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Currency;
use Tests\Fixtures\BankAccount\Infrastructure\Repositories\InMemoryBankAccountQueryRepository;
use Tests\Fixtures\BankAccount\Infrastructure\Repositories\InMemoryBankAccountRepository;

final class QueryRepositoryTest extends TestCase
{
    private InMemoryBankAccountRepository $aggregateRepository;

    private BankAccountQueryRepository $queryRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->aggregateRepository = new InMemoryBankAccountRepository();
        $this->queryRepository = new InMemoryBankAccountQueryRepository($this->aggregateRepository);
    }

    public function testGetByIdReturnsProjectionWhenAggregateExists(): void
    {
        $account = BankAccount::create();
        $this->aggregateRepository->save($account);

        $projection = $this->queryRepository->getById($account->id->value);

        $this->assertInstanceOf(BankAccountProjection::class, $projection);
        $this->assertSame($account->id->value, $projection->id);
        $this->assertSame(0, $projection->balanceAmount);
        $this->assertSame('USD', $projection->currency);
        $this->assertSame([], $projection->transactions);
    }

    public function testGetByIdReturnsNullWhenAggregateDoesNotExist(): void
    {
        $projection = $this->queryRepository->getById('acc-nonexistent');

        $this->assertNull($projection);
    }

    public function testFilterReturnsMatchingProjectionsAsArray(): void
    {
        $balanceZero = AccountBalance::zero();
        $balance100 = new AccountBalance(100, Currency::USD);
        $balance200 = new AccountBalance(200, Currency::USD);
        $this->aggregateRepository->save(BankAccount::build(
            BankAccountId::fromString('acc-low'),
            $balanceZero,
            []
        ));
        $this->aggregateRepository->save(BankAccount::build(
            BankAccountId::fromString('acc-mid'),
            $balance100,
            []
        ));
        $this->aggregateRepository->save(BankAccount::build(
            BankAccountId::fromString('acc-high'),
            $balance200,
            []
        ));

        $result = $this->queryRepository->filter(
            0,
            10,
            [new BankAccountFilterCriteria('balanceAmount', FilterOperator::GTE, 100)]
        );

        $this->assertCount(2, $result);
        $ids = array_map(fn (BankAccountProjection $p) => $p->id, $result);
        $this->assertContains('acc-mid', $ids);
        $this->assertContains('acc-high', $ids);
        $this->assertNotContains('acc-low', $ids);
    }

    public function testFilterRespectsOffsetAndLimit(): void
    {
        $balance100 = new AccountBalance(100, Currency::USD);
        for ($i = 0; $i < 5; $i++) {
            $this->aggregateRepository->save(BankAccount::build(
                BankAccountId::fromString('acc-' . $i),
                $balance100,
                []
            ));
        }

        $result = $this->queryRepository->filter(1, 2, [
            new BankAccountFilterCriteria('balanceAmount', FilterOperator::EQ, 100),
        ]);

        $this->assertCount(2, $result);
    }
}
