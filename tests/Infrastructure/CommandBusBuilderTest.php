<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\CommandBus;
use SeedWork\Application\DomainEventBus;
use SeedWork\Application\Result;
use SeedWork\Domain\UnitOfWork;
use SeedWork\Infrastructure\CommandBusBuilder;
use SeedWork\Infrastructure\DeferredDomainEventBus;
use SeedWork\Infrastructure\DomainEventCoordinatorCommandBus;
use SeedWork\Infrastructure\RegistryCommandBus;
use SeedWork\Infrastructure\TransactionalCommandBus;
use SeedWork\Infrastructure\ValidationCommandBus;

final class CommandBusBuilderTest extends TestCase
{
    public function testBuildWithNoStepsReturnsRegistryDirectly(): void
    {
        $registry = new RegistryCommandBus();

        $result = (new CommandBusBuilder($registry))->build();

        self::assertSame($registry, $result);
    }

    public function testRegistryReturnsInjectedInstance(): void
    {
        $registry = new RegistryCommandBus();

        $builder = new CommandBusBuilder($registry);

        self::assertSame($registry, $builder->registry());
    }

    public function testRegistryRemainsTheSameAfterAddingSteps(): void
    {
        $registry = new RegistryCommandBus();
        $builder = new CommandBusBuilder($registry);
        $unitOfWork = $this->createStub(UnitOfWork::class);
        $deferredEventBus = new DeferredDomainEventBus();

        $builder
            ->withDomainEventCoordination($deferredEventBus)
            ->withTransaction($unitOfWork)
            ->withValidation();

        self::assertSame($registry, $builder->registry());
    }

    public function testWithTransactionalProducesTransactionalCommandBus(): void
    {
        $unitOfWork = $this->createStub(UnitOfWork::class);

        $result = (new CommandBusBuilder(new RegistryCommandBus()))
            ->withTransaction($unitOfWork)
            ->build();

        self::assertInstanceOf(TransactionalCommandBus::class, $result);
    }

    public function testWithValidationProducesValidationCommandBus(): void
    {
        $result = (new CommandBusBuilder(new RegistryCommandBus()))
            ->withValidation()
            ->build();

        self::assertInstanceOf(ValidationCommandBus::class, $result);
    }

    public function testWithDomainEventCoordinationProducesDomainEventCoordinatorCommandBus(): void
    {
        $result = (new CommandBusBuilder(new RegistryCommandBus()))
            ->withDomainEventCoordination(new DeferredDomainEventBus())
            ->build();

        self::assertInstanceOf(DomainEventCoordinatorCommandBus::class, $result);
    }

    public function testWithDomainEventCoordinationAcceptsDomainEventBusInterface(): void
    {
        $eventBus = $this->createStub(DomainEventBus::class);

        $result = (new CommandBusBuilder(new RegistryCommandBus()))
            ->withDomainEventCoordination($eventBus)
            ->build();

        self::assertInstanceOf(DomainEventCoordinatorCommandBus::class, $result);
    }

    public function testFirstStepAddedBecomesOutermostDecorator(): void
    {
        $unitOfWork = $this->createStub(UnitOfWork::class);
        $deferredEventBus = new DeferredDomainEventBus();

        $result = (new CommandBusBuilder(new RegistryCommandBus()))
            ->withValidation()
            ->withTransaction($unitOfWork)
            ->withDomainEventCoordination($deferredEventBus)
            ->build();

        self::assertInstanceOf(ValidationCommandBus::class, $result);
    }

    public function testUseAppliesCustomMiddleware(): void
    {
        $customBus = $this->createMock(CommandBus::class);
        $customBus->method('dispatch')->willReturn(Result::ok());

        $result = (new CommandBusBuilder(new RegistryCommandBus()))
            ->use(fn (CommandBus $inner): CommandBus => $customBus)
            ->build();

        self::assertSame($customBus, $result);
    }

    public function testUseCanBeChainedWithNamedDecorators(): void
    {
        $unitOfWork = $this->createStub(UnitOfWork::class);
        $customWrapper = $this->createMock(CommandBus::class);
        $customWrapper->method('dispatch')->willReturn(Result::ok());

        $result = (new CommandBusBuilder(new RegistryCommandBus()))
            ->withValidation()
            ->withTransaction($unitOfWork)
            ->use(fn (CommandBus $inner): CommandBus => $customWrapper)
            ->build();

        self::assertInstanceOf(ValidationCommandBus::class, $result);
    }
}
