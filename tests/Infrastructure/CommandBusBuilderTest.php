<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\CommandBus;
use SeedWork\Application\DomainEventBus;
use SeedWork\Domain\UnitOfWork;
use SeedWork\Infrastructure\CommandBusBuilder;
use SeedWork\Infrastructure\DeferredDomainEventBus;
use SeedWork\Infrastructure\DomainEventCoordinatorCommandBus;
use SeedWork\Infrastructure\RegistryCommandBus;
use SeedWork\Infrastructure\TransactionalCommandBus;
use SeedWork\Infrastructure\ValidationCommandBus;

final class CommandBusBuilderTest extends TestCase
{
    public function testBuildReturnsBaseCommandBusWhenNoDecoratorsAdded(): void
    {
        $innerBus = $this->createStub(CommandBus::class);

        $result = CommandBusBuilder::from($innerBus)->build();

        self::assertSame($innerBus, $result);
    }

    public function testNewCreatesRegistryCommandBusAsDefault(): void
    {
        $result = CommandBusBuilder::new()->build();

        self::assertInstanceOf(RegistryCommandBus::class, $result);
    }

    public function testWithTransactionalWrapsCurrentBus(): void
    {
        $innerBus = $this->createStub(CommandBus::class);
        $unitOfWork = $this->createStub(UnitOfWork::class);

        $result = CommandBusBuilder::from($innerBus)->withTransactional($unitOfWork)->build();

        self::assertInstanceOf(TransactionalCommandBus::class, $result);
    }

    public function testWithValidationWrapsCurrentBus(): void
    {
        $innerBus = $this->createStub(CommandBus::class);

        $result = CommandBusBuilder::from($innerBus)->withValidation()->build();

        self::assertInstanceOf(ValidationCommandBus::class, $result);
    }

    public function testWithDomainEventCoordinationWrapsCurrentBus(): void
    {
        $innerBus = $this->createStub(CommandBus::class);
        $deferredEventBus = new DeferredDomainEventBus();

        $result = CommandBusBuilder::from($innerBus)->withDomainEventCoordination($deferredEventBus)->build();

        self::assertInstanceOf(DomainEventCoordinatorCommandBus::class, $result);
    }

    public function testWithDomainEventCoordinationAcceptsDomainEventBusInterface(): void
    {
        $innerBus = $this->createStub(CommandBus::class);
        $eventBus = $this->createStub(DomainEventBus::class);

        $result = CommandBusBuilder::from($innerBus)->withDomainEventCoordination($eventBus)->build();

        self::assertInstanceOf(DomainEventCoordinatorCommandBus::class, $result);
    }

    public function testFullChainOutermostLayerIsValidation(): void
    {
        $innerBus = $this->createStub(CommandBus::class);
        $unitOfWork = $this->createStub(UnitOfWork::class);
        $deferredEventBus = new DeferredDomainEventBus();

        $result = CommandBusBuilder::from($innerBus)
            ->withDomainEventCoordination($deferredEventBus)
            ->withTransactional($unitOfWork)
            ->withValidation()
            ->build();

        self::assertInstanceOf(ValidationCommandBus::class, $result);
    }
}
