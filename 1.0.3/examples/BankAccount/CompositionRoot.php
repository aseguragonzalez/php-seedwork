<?php

declare(strict_types=1);

namespace Examples\BankAccount;

use Examples\BankAccount\Application\AccountOpened\AccountOpenedDomainEventHandler;
use Examples\BankAccount\Application\DepositMoney\DepositMoneyCommand;
use Examples\BankAccount\Application\DepositMoney\DepositMoneyCommandHandler;
use Examples\BankAccount\Application\GetBankAccountStatus\GetBankAccountStatusQuery;
use Examples\BankAccount\Application\GetBankAccountStatus\GetBankAccountStatusQueryHandler;
use Examples\BankAccount\Application\OpenAccount\OpenAccountCommand;
use Examples\BankAccount\Application\OpenAccount\OpenAccountCommandHandler;
use Examples\BankAccount\Application\WithdrawMoney\WithdrawMoneyCommand;
use Examples\BankAccount\Application\WithdrawMoney\WithdrawMoneyCommandHandler;
use Examples\BankAccount\Domain\Events\AccountOpened;
use Examples\BankAccount\Infrastructure\Repositories\InMemoryBankAccountRepository;
use Examples\BankAccount\Infrastructure\Repositories\PublishingBankAccountRepository;
use SeedWork\Application\CommandBus;
use SeedWork\Application\QueryBus;
use SeedWork\Infrastructure\CommandBusBuilder;
use SeedWork\Infrastructure\DeferredDomainEventBus;
use SeedWork\Infrastructure\QueryBusBuilder;
use SeedWork\Infrastructure\RegistryCommandBus;
use SeedWork\Infrastructure\RegistryQueryBus;
use SeedWork\Testing\InMemoryIntegrationEventPublisher;

/**
 * Composition root for the BankAccount example.
 *
 * Demonstrates how to wire a CommandBus and QueryBus pipeline using post-Decision 7 and 8
 * conventions: RegistryCommandBus (no PSR-11), DeferredDomainEventBus with direct handler
 * instances, PublishingBankAccountRepository, and CommandBusBuilder::withDomainEventCoordination().
 *
 * A single shared repository instance is wired into both buses so that commands and
 * queries operate on the same in-memory state.
 */
final class CompositionRoot
{
    private InMemoryBankAccountRepository $repository;
    private DeferredDomainEventBus $domainEventBus;
    private CommandBus $commandBus;
    private QueryBus $queryBus;

    public function __construct()
    {
        $this->repository = new InMemoryBankAccountRepository();
        $publisher = new InMemoryIntegrationEventPublisher();

        $this->domainEventBus = new DeferredDomainEventBus();
        $this->domainEventBus->subscribe(
            AccountOpened::class,
            new AccountOpenedDomainEventHandler($publisher)
        );

        $publishingRepository = new PublishingBankAccountRepository(
            $this->repository,
            $this->domainEventBus
        );

        $commandRegistry = new RegistryCommandBus();
        $commandRegistry->register(OpenAccountCommand::class, new OpenAccountCommandHandler($publishingRepository));
        $commandRegistry->register(DepositMoneyCommand::class, new DepositMoneyCommandHandler($publishingRepository));
        $commandRegistry->register(WithdrawMoneyCommand::class, new WithdrawMoneyCommandHandler($publishingRepository));

        $this->commandBus = (new CommandBusBuilder($commandRegistry))
            ->withDomainEventCoordination($this->domainEventBus)
            ->build()
        ;

        $queryRegistry = new RegistryQueryBus();
        $queryRegistry->register(
            GetBankAccountStatusQuery::class,
            new GetBankAccountStatusQueryHandler($this->repository)
        );

        $this->queryBus = (new QueryBusBuilder($queryRegistry))
            ->build()
        ;
    }

    public function commandBus(): CommandBus
    {
        return $this->commandBus;
    }

    public function queryBus(): QueryBus
    {
        return $this->queryBus;
    }
}
