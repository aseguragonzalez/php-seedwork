<?php

declare(strict_types=1);

namespace Examples\BankAccount\Tests;

use PHPUnit\Framework\TestCase;
use SeedWork\Infrastructure\CommandBusBuilder;
use SeedWork\Infrastructure\DeferredDomainEventBus;
use Examples\BankAccount\Infrastructure\Repositories\PublishingBankAccountRepository;
use SeedWork\Infrastructure\RegistryCommandBus;
use Examples\BankAccount\Application\DepositMoney\DepositMoneyCommand;
use Examples\BankAccount\Application\DepositMoney\DepositMoneyCommandHandler;
use Examples\BankAccount\Domain\Entities\BankAccount;
use Examples\BankAccount\Domain\ValueObjects\AccountBalance;
use Examples\BankAccount\Domain\ValueObjects\Currency;
use Examples\BankAccount\Infrastructure\Repositories\InMemoryBankAccountRepository;

final class DepositMoneyHandlerTest extends TestCase
{
    public function testDepositUpdatesBalanceAndReturnsOk(): void
    {
        $repository = new InMemoryBankAccountRepository();
        $domainEventBus = new DeferredDomainEventBus();
        $publishingRepo = new PublishingBankAccountRepository($repository, $domainEventBus);

        $account = BankAccount::create(initialBalance: new AccountBalance(100, Currency::USD));
        $repository->save($account);

        $registry = new RegistryCommandBus();
        $registry->register(
            DepositMoneyCommand::class,
            new DepositMoneyCommandHandler($publishingRepo)
        );
        $bus = (new CommandBusBuilder($registry))
            ->withValidation()
            ->withDomainEventCoordination($domainEventBus)
            ->build();

        $result = $bus->dispatch(new DepositMoneyCommand($account->id->value, 50, 'USD'));

        $this->assertTrue($result->isOk());

        $updated = $repository->findById($account->id);
        $this->assertNotNull($updated);
        $this->assertSame(150, $updated->getBalance()->amount);
    }
}
