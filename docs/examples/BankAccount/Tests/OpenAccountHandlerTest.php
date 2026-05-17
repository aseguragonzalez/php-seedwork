<?php

declare(strict_types=1);

namespace Examples\BankAccount\Tests;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\ValidationErrors;
use SeedWork\Infrastructure\CommandBusBuilder;
use SeedWork\Infrastructure\DeferredDomainEventBus;
use SeedWork\Infrastructure\RegistryCommandBus;
use Examples\BankAccount\Application\OpenAccount\OpenAccountCommand;
use Examples\BankAccount\Application\OpenAccount\OpenAccountCommandHandler;
use Examples\BankAccount\Infrastructure\Repositories\InMemoryBankAccountRepository;
use Examples\BankAccount\Infrastructure\Repositories\PublishingBankAccountRepository;

final class OpenAccountHandlerTest extends TestCase
{
    public function testValidCommandPersistsAccountAndReturnsOk(): void
    {
        $repository = new InMemoryBankAccountRepository();
        $domainEventBus = new DeferredDomainEventBus();
        $publishingRepo = new PublishingBankAccountRepository($repository, $domainEventBus);

        $registry = new RegistryCommandBus();
        $registry->register(
            OpenAccountCommand::class,
            new OpenAccountCommandHandler($publishingRepo)
        );
        $bus = (new CommandBusBuilder($registry))
            ->withDomainEventCoordination($domainEventBus)
            ->build();

        $result = $bus->dispatch(new OpenAccountCommand('USD'));

        $this->assertTrue($result->isOk());
        $this->assertCount(1, $repository->findAll());
    }

    public function testEmptyCurrencyFailsValidationOnInstantiation(): void
    {
        $this->expectException(ValidationErrors::class);

        new OpenAccountCommand('');
    }
}
