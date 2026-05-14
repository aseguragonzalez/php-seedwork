<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\Maybe;
use SeedWork\Application\QueryHandler;
use SeedWork\Infrastructure\RegistryQueryBus;
use Examples\BankAccount\Application\GetBankAccountStatus\BankAccountStatusResult;
use Examples\BankAccount\Application\GetBankAccountStatus\GetBankAccountStatusQuery;
use Examples\BankAccount\Application\GetBankAccountStatus\GetBankAccountStatusQueryHandler;
use Examples\BankAccount\Domain\Entities\BankAccount;
use Examples\BankAccount\Domain\Entities\BankAccountId;
use Examples\BankAccount\Domain\ValueObjects\AccountBalance;
use Examples\BankAccount\Infrastructure\Repositories\InMemoryBankAccountRepository;

final class RegistryQueryBusTest extends TestCase
{
    public function testAskInvokesRegisteredHandlerAndReturnsMaybe(): void
    {
        $query = $this->createQuery();
        $expectedMaybe = Maybe::just($this->createStubResult());
        $handler = $this->createStub(QueryHandler::class);
        $handler->method('handle')->willReturn($expectedMaybe);

        $bus = new RegistryQueryBus();
        $bus->register(GetBankAccountStatusQuery::class, $handler);

        $result = $bus->ask($query);

        $this->assertSame($expectedMaybe, $result);
    }

    public function testAskThrowsLogicExceptionWhenNoHandlerRegistered(): void
    {
        $query = $this->createQuery();
        $bus = new RegistryQueryBus();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('No handler for');

        $bus->ask($query);
    }

    public function testAskWithRealHandlerReturnsNothingWhenAccountNotFound(): void
    {
        $repository = new InMemoryBankAccountRepository();
        $handler = new GetBankAccountStatusQueryHandler($repository);

        $bus = new RegistryQueryBus();
        $bus->register(GetBankAccountStatusQuery::class, $handler);

        $maybe = $bus->ask(new GetBankAccountStatusQuery(BankAccountId::create()->value));

        $this->assertFalse($maybe->hasValue());
    }

    public function testAskWithRealHandlerReturnsJustWhenAccountExists(): void
    {
        $repository = new InMemoryBankAccountRepository();
        $account = BankAccount::create();
        $repository->save($account);

        $handler = new GetBankAccountStatusQueryHandler($repository);

        $bus = new RegistryQueryBus();
        $bus->register(GetBankAccountStatusQuery::class, $handler);

        $maybe = $bus->ask(new GetBankAccountStatusQuery($account->id->value));

        $this->assertTrue($maybe->hasValue());
        $this->assertInstanceOf(BankAccountStatusResult::class, $maybe->value());
    }

    private function createQuery(): GetBankAccountStatusQuery
    {
        return new GetBankAccountStatusQuery(BankAccountId::create()->value);
    }

    private function createStubResult(): BankAccountStatusResult
    {
        return new BankAccountStatusResult(
            BankAccountId::create(),
            AccountBalance::zero(),
            []
        );
    }
}
