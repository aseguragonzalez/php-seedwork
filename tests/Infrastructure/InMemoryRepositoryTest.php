<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Infrastructure\InMemoryRepository;
use Examples\BankAccount\Domain\Entities\BankAccount;
use Examples\BankAccount\Domain\Entities\BankAccountId;

final class InMemoryRepositoryTest extends TestCase
{
    public function testFindByReturnsNullWhenNotFound(): void
    {
        $repo = new InMemoryRepository();

        $result = $repo->findBy(BankAccountId::create());

        $this->assertNull($result);
    }

    public function testSaveAndFindByHit(): void
    {
        $repo = new InMemoryRepository();
        $account = BankAccount::create();

        $repo->save($account);
        $result = $repo->findBy($account->id);

        $this->assertNotNull($result);
        $this->assertTrue($result->id->equals($account->id));
    }

    public function testDeleteByRemovesAggregate(): void
    {
        $repo = new InMemoryRepository();
        $account = BankAccount::create();
        $repo->save($account);

        $repo->deleteBy($account->id);

        $this->assertNull($repo->findBy($account->id));
    }

    public function testSaveOverwritesExistingAggregate(): void
    {
        $repo = new InMemoryRepository();
        $account1 = BankAccount::create();
        $repo->save($account1);

        // Save the same ID again (simulating an update)
        $repo->save($account1);

        $result = $repo->findBy($account1->id);
        $this->assertNotNull($result);
    }
}
