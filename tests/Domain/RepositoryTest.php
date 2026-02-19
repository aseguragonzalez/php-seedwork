<?php

declare(strict_types=1);

namespace Tests\Domain;

use PHPUnit\Framework\TestCase;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccount;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccountId;
use Tests\Fixtures\BankAccount\Domain\Repositories\BankAccountRepository;
use Tests\Fixtures\BankAccount\Infrastructure\Repositories\InMemoryBankAccountRepository;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Currency;
use Tests\Fixtures\BankAccount\Domain\ValueObjects\Money;

final class RepositoryTest extends TestCase
{
    private BankAccountRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new InMemoryBankAccountRepository();
    }

    public function testSaveAndGetById(): void
    {
        $id = BankAccountId::fromString('acc-001');
        $account = BankAccount::create($id)->deposit(new Money(100, Currency::USD));

        $this->repository->save($account);

        $loaded = $this->repository->getById($id);

        $this->assertInstanceOf(BankAccount::class, $loaded);
        $this->assertTrue($loaded->id->equals($id));
        $this->assertEquals(100, $loaded->getBalance()->amount);
    }

    public function testGetByIdUpdatesWhenSaveCalled(): void
    {
        $id = BankAccountId::fromString('acc-001');
        $account = BankAccount::create($id)->deposit(new Money(100, Currency::USD));

        $this->repository->save($account);
        $account = $account->withdraw(new Money(30, Currency::USD));
        $this->repository->save($account);

        $loaded = $this->repository->getById($id);

        $this->assertEquals(70, $loaded->getBalance()->amount);
    }

    public function testDeleteById(): void
    {
        $id = BankAccountId::fromString('acc-001');
        $account = BankAccount::create($id);

        $this->repository->save($account);
        $this->repository->deleteById($id);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('BankAccount not found');

        $this->repository->getById($id);
    }

    public function testGetByIdNotFoundThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('BankAccount not found');

        $this->repository->getById(BankAccountId::fromString('non-existent'));
    }
}
