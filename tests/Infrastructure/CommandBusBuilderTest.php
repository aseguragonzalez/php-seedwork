<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\CommandBus;
use SeedWork\Application\CommandValidator;
use SeedWork\Domain\UnitOfWork;
use SeedWork\Infrastructure\CommandBusBuilder;
use SeedWork\Infrastructure\DeferredDomainEventBus;
use SeedWork\Infrastructure\DomainEventFlushCommandBus;
use SeedWork\Infrastructure\TransactionalCommandBus;
use SeedWork\Infrastructure\ValidationCommandBus;
use Tests\Fixtures\FakeContainer;

final class CommandBusBuilderTest extends TestCase
{
    public function testBuildReturnsBaseCommandBusWhenNoDecoratorsAdded(): void
    {
        $innerBus = $this->createMock(CommandBus::class);

        $result = CommandBusBuilder::from($innerBus)->build();

        self::assertSame($innerBus, $result);
    }

    public function testWithTransactionalWrapsCurrentBus(): void
    {
        $innerBus = $this->createMock(CommandBus::class);
        $unitOfWork = $this->createMock(UnitOfWork::class);

        $result = CommandBusBuilder::from($innerBus)->withTransactional($unitOfWork)->build();

        self::assertInstanceOf(TransactionalCommandBus::class, $result);
    }

    public function testWithValidationWrapsCurrentBus(): void
    {
        $innerBus = $this->createMock(CommandBus::class);
        $validator = $this->createMock(CommandValidator::class);

        $result = CommandBusBuilder::from($innerBus)->withValidation($validator)->build();

        self::assertInstanceOf(ValidationCommandBus::class, $result);
    }

    public function testWithEventFlushingWrapsCurrentBus(): void
    {
        $innerBus = $this->createMock(CommandBus::class);
        $deferredEventBus = new DeferredDomainEventBus(new FakeContainer([]));

        $result = CommandBusBuilder::from($innerBus)->withEventFlushing($deferredEventBus)->build();

        self::assertInstanceOf(DomainEventFlushCommandBus::class, $result);
    }

    public function testFullChainOutermostLayerIsValidation(): void
    {
        $innerBus = $this->createMock(CommandBus::class);
        $unitOfWork = $this->createMock(UnitOfWork::class);
        $validator = $this->createMock(CommandValidator::class);
        $deferredEventBus = new DeferredDomainEventBus(new FakeContainer([]));

        $result = CommandBusBuilder::from($innerBus)
            ->withEventFlushing($deferredEventBus)
            ->withTransactional($unitOfWork)
            ->withValidation($validator)
            ->build();

        self::assertInstanceOf(ValidationCommandBus::class, $result);
    }
}
