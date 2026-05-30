<?php

declare(strict_types=1);

namespace Examples\BankAccount\Tests;

use Examples\BankAccount\Application\TransferMoney\TransferMoneyCommand;
use Examples\BankAccount\Application\TransferMoney\TransferMoneyCommandHandler;
use Examples\BankAccount\Domain\Entities\BankAccount;
use Examples\BankAccount\Domain\ValueObjects\AccountBalance;
use Examples\BankAccount\Domain\ValueObjects\Currency;
use Examples\BankAccount\Infrastructure\Repositories\InMemoryBankAccountRepository;
use Examples\BankAccount\Infrastructure\Repositories\PublishingBankAccountRepository;
use PHPUnit\Framework\TestCase;
use SeedWork\Application\CommandBus;
use SeedWork\Infrastructure\CommandBusBuilder;
use SeedWork\Infrastructure\DeferredDomainEventBus;
use SeedWork\Infrastructure\RegistryCommandBus;

/**
 * @internal
 *
 * @coversNothing
 */
final class TransferMoneyHandlerTest extends TestCase
{
    public function testTransferUpdatesBothBalancesAndReturnsOk(): void
    {
        $repository = new InMemoryBankAccountRepository();
        $domainEventBus = new DeferredDomainEventBus();

        $from = BankAccount::create(initialBalance: new AccountBalance(300, Currency::USD));
        $to = BankAccount::create(initialBalance: new AccountBalance(50, Currency::USD));
        $repository->save($from);
        $repository->save($to);

        $bus = $this->buildBus($repository, $domainEventBus);
        $result = $bus->dispatch(new TransferMoneyCommand($from->id->value, $to->id->value, 100, 'USD'));

        $this->assertTrue($result->isOk());

        $updatedFrom = $repository->findById($from->id);
        $updatedTo = $repository->findById($to->id);
        $this->assertNotNull($updatedFrom);
        $this->assertNotNull($updatedTo);
        $this->assertSame(200, $updatedFrom->getBalance()->amount);
        $this->assertSame(150, $updatedTo->getBalance()->amount);
    }

    public function testTransferMissingSourceAccountReturnsFailure(): void
    {
        $repository = new InMemoryBankAccountRepository();
        $domainEventBus = new DeferredDomainEventBus();

        $to = BankAccount::create(initialBalance: new AccountBalance(50, Currency::USD));
        $repository->save($to);

        $bus = $this->buildBus($repository, $domainEventBus);
        $result = $bus->dispatch(new TransferMoneyCommand('non-existent-id', $to->id->value, 100, 'USD'));

        $this->assertFalse($result->isOk());
    }

    public function testTransferMissingDestinationAccountReturnsFailure(): void
    {
        $repository = new InMemoryBankAccountRepository();
        $domainEventBus = new DeferredDomainEventBus();

        $from = BankAccount::create(initialBalance: new AccountBalance(300, Currency::USD));
        $repository->save($from);

        $bus = $this->buildBus($repository, $domainEventBus);
        $result = $bus->dispatch(new TransferMoneyCommand($from->id->value, 'non-existent-id', 100, 'USD'));

        $this->assertFalse($result->isOk());
    }

    private function buildBus(
        InMemoryBankAccountRepository $repository,
        DeferredDomainEventBus $domainEventBus,
    ): CommandBus {
        $publishingRepo = new PublishingBankAccountRepository($repository, $domainEventBus);

        $registry = new RegistryCommandBus();
        $registry->register(
            TransferMoneyCommand::class,
            new TransferMoneyCommandHandler($publishingRepo)
        );

        return (new CommandBusBuilder($registry))
            ->withDomainEventCoordination($domainEventBus)
            ->build()
        ;
    }
}
