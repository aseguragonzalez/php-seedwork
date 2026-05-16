<?php

declare(strict_types=1);

namespace Examples\BankAccount\Tests;

use PHPUnit\Framework\TestCase;
use SeedWork\Infrastructure\CommandBusBuilder;
use SeedWork\Infrastructure\DeferredDomainEventBus;
use Examples\BankAccount\Infrastructure\Repositories\PublishingBankAccountRepository;
use SeedWork\Infrastructure\RegistryCommandBus;
use Examples\BankAccount\Application\WithdrawMoney\WithdrawMoneyCommand;
use Examples\BankAccount\Application\WithdrawMoney\WithdrawMoneyCommandHandler;
use Examples\BankAccount\Domain\Entities\BankAccount;
use Examples\BankAccount\Domain\ValueObjects\AccountBalance;
use Examples\BankAccount\Domain\ValueObjects\Currency;
use Examples\BankAccount\Infrastructure\Repositories\InMemoryBankAccountRepository;

final class WithdrawMoneyHandlerTest extends TestCase
{
    public function testWithdrawUpdatesBalanceAndReturnsOk(): void
    {
        $repository = new InMemoryBankAccountRepository();
        $domainEventBus = new DeferredDomainEventBus();
        $publishingRepo = new PublishingBankAccountRepository($repository, $domainEventBus);

        $account = BankAccount::create(initialBalance: new AccountBalance(200, Currency::USD));
        $repository->save($account);

        $registry = new RegistryCommandBus();
        $registry->register(
            WithdrawMoneyCommand::class,
            new WithdrawMoneyCommandHandler($publishingRepo)
        );
        $bus = (new CommandBusBuilder($registry))
            ->withValidation()
            ->withDomainEventCoordination($domainEventBus)
            ->build();

        $result = $bus->dispatch(new WithdrawMoneyCommand($account->id->value, 80, 'USD'));

        $this->assertTrue($result->isOk());

        $updated = $repository->findById($account->id);
        $this->assertNotNull($updated);
        $this->assertSame(120, $updated->getBalance()->amount);
    }

    public function testWithdrawMissingAccountReturnsFailure(): void
    {
        $repository = new InMemoryBankAccountRepository();
        $domainEventBus = new DeferredDomainEventBus();
        $publishingRepo = new PublishingBankAccountRepository($repository, $domainEventBus);

        $registry = new RegistryCommandBus();
        $registry->register(
            WithdrawMoneyCommand::class,
            new WithdrawMoneyCommandHandler($publishingRepo)
        );
        $bus = (new CommandBusBuilder($registry))
            ->withValidation()
            ->withDomainEventCoordination($domainEventBus)
            ->build();

        $result = $bus->dispatch(new WithdrawMoneyCommand('non-existent-id', 50, 'USD'));

        $this->assertFalse($result->isOk());
    }

    public function testWithdrawInsufficientFundsReturnsFailure(): void
    {
        $repository = new InMemoryBankAccountRepository();
        $domainEventBus = new DeferredDomainEventBus();
        $publishingRepo = new PublishingBankAccountRepository($repository, $domainEventBus);

        $account = BankAccount::create(initialBalance: new AccountBalance(30, Currency::USD));
        $repository->save($account);

        $registry = new RegistryCommandBus();
        $registry->register(
            WithdrawMoneyCommand::class,
            new WithdrawMoneyCommandHandler($publishingRepo)
        );
        $bus = (new CommandBusBuilder($registry))
            ->withValidation()
            ->withDomainEventCoordination($domainEventBus)
            ->build();

        $result = $bus->dispatch(new WithdrawMoneyCommand($account->id->value, 100, 'USD'));

        $this->assertFalse($result->isOk());
    }
}
