<?php

declare(strict_types=1);

namespace Tests\Domain;

use PHPUnit\Framework\TestCase;
use SeedWork\Domain\Exceptions\NotFoundResource;
use Tests\Fixtures\BankAccount\Domain\BankAccountObtainer;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccount;
use Tests\Fixtures\BankAccount\Domain\Entities\BankAccountId;
use Tests\Fixtures\BankAccount\Infrastructure\Repositories\InMemoryBankAccountRepository;

final class AggregateObtainerTest extends TestCase
{
    public function testObtainReturnsAggregateWhenFound(): void
    {
        $account = BankAccount::create();
        $repository = new InMemoryBankAccountRepository();
        $repository->save($account);
        $obtainer = new BankAccountObtainer($repository);

        $result = $obtainer->obtain($account->id);

        $this->assertInstanceOf(BankAccount::class, $result);
        $this->assertTrue($result->id->equals($account->id));
    }

    public function testObtainThrowsNotFoundResourceWhenNotFound(): void
    {
        $repository = new InMemoryBankAccountRepository();
        $obtainer = new BankAccountObtainer($repository);
        $id = BankAccountId::fromString('acc-nonexistent');

        $this->expectException(NotFoundResource::class);
        $this->expectExceptionMessage("Resource 'BankAccount' not found for id 'acc-nonexistent'");

        $obtainer->obtain($id);
    }

    public function testObtainThrowsNotFoundResourceWithResourceNameInMessage(): void
    {
        $repository = new InMemoryBankAccountRepository();
        $obtainer = new BankAccountObtainer($repository);
        $id = BankAccountId::fromString('acc-xyz');

        $exception = null;
        try {
            $obtainer->obtain($id);
        } catch (NotFoundResource $e) {
            $exception = $e;
        }

        $this->assertNotNull($exception);
        $this->assertStringContainsString('BankAccount', $exception->getMessage());
    }
}
