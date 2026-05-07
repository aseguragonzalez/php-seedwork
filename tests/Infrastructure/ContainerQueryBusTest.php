<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\QueryHandler;
use SeedWork\Application\QueryResult;
use SeedWork\Infrastructure\ContainerQueryBus;
use Examples\BankAccount\Application\GetBankAccountStatus\GetBankAccountStatusQueryHandler;
use Examples\BankAccount\Application\GetBankAccountStatus\BankAccountStatusResult;
use Examples\BankAccount\Application\GetBankAccountStatus\GetBankAccountStatusQuery;
use Examples\BankAccount\Infrastructure\Repositories\InMemoryBankAccountRepository;
use Examples\BankAccount\Domain\Entities\BankAccount;
use Examples\BankAccount\Domain\Entities\BankAccountId;
use Examples\BankAccount\Domain\ValueObjects\AccountBalance;
use Tests\Fixtures\FakeContainer;

final class ContainerQueryBusTest extends TestCase
{
    public function testAskReturnsResultFromRegisteredHandler(): void
    {
        $query = $this->createGetBankAccountStatusQuery();
        $stubResult = $this->createStubQueryResult();
        $handler = $this->createStub(QueryHandler::class);
        $handler->method('handle')->willReturn($stubResult);
        $container = new FakeContainer(['statusHandler' => $handler]);
        $bus = new ContainerQueryBus($container);
        $bus->register(GetBankAccountStatusQuery::class, 'statusHandler');

        $result = $bus->ask($query);

        $this->assertSame($stubResult, $result);
    }

    public function testAskInvokesHandlerWithQuery(): void
    {
        $query = $this->createGetBankAccountStatusQuery();
        $handler = $this->createMock(QueryHandler::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($query)
            ->willReturn($this->createStubQueryResult());
        $container = new FakeContainer(['statusHandler' => $handler]);
        $bus = new ContainerQueryBus($container);
        $bus->register(GetBankAccountStatusQuery::class, 'statusHandler');

        $bus->ask($query);
    }

    public function testAskThrowsWhenNoHandlerRegistered(): void
    {
        $query = $this->createGetBankAccountStatusQuery();
        $container = new FakeContainer([]);
        $bus = new ContainerQueryBus($container);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'No handler registered for query ' .
            'Examples\BankAccount\Application\GetBankAccountStatus\GetBankAccountStatusQuery.'
        );

        $bus->ask($query);
    }

    public function testAskThrowsWhenContainerReturnsNonQueryHandler(): void
    {
        $query = $this->createGetBankAccountStatusQuery();
        $container = new FakeContainer([
            'statusHandler' => new \stdClass(),
        ]);
        $bus = new ContainerQueryBus($container);
        $bus->register(GetBankAccountStatusQuery::class, 'statusHandler');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Handler for query type ' .
            'Examples\BankAccount\Application\GetBankAccountStatus' .
            '\GetBankAccountStatusQuery is not a valid handler.'
        );

        $bus->ask($query);
    }

    private function createGetBankAccountStatusQuery(): GetBankAccountStatusQuery
    {
        return new GetBankAccountStatusQuery(BankAccountId::create()->value);
    }

    private function createStubQueryResult(): QueryResult
    {
        return new BankAccountStatusResult(
            BankAccountId::create(),
            AccountBalance::zero(),
            []
        );
    }

    public function testGetBankAccountStatusQueryHandlerReturnsAggregateData(): void
    {
        $repository = new InMemoryBankAccountRepository();
        $account = BankAccount::create();
        $repository->save($account);

        $handler = new GetBankAccountStatusQueryHandler($repository);
        $container = new FakeContainer(['statusHandler' => $handler]);
        $bus = new ContainerQueryBus($container);
        $bus->register(GetBankAccountStatusQuery::class, 'statusHandler');

        $query = new GetBankAccountStatusQuery($account->id->value);
        $result = $bus->ask($query);

        $this->assertInstanceOf(BankAccountStatusResult::class, $result);
        $this->assertTrue($result->accountId->equals($account->id));
        $this->assertSame(0, $result->balance->amount);
        $this->assertSame([], $result->transactions);
    }
}
