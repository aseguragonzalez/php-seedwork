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
        $innerBus = $this->createStub(CommandBus::class);

        $result = CommandBusBuilder::from($innerBus)->build();

        self::assertSame($innerBus, $result);
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
        $validator = $this->createStub(CommandValidator::class);

        $result = CommandBusBuilder::from($innerBus)->withValidation($validator)->build();

        self::assertInstanceOf(ValidationCommandBus::class, $result);
    }

    public function testWithEventFlushingWrapsCurrentBus(): void
    {
        $innerBus = $this->createStub(CommandBus::class);
        $deferredEventBus = new DeferredDomainEventBus(new FakeContainer([]));

        $result = CommandBusBuilder::from($innerBus)->withEventFlushing($deferredEventBus)->build();

        self::assertInstanceOf(DomainEventFlushCommandBus::class, $result);
    }

    public function testFullChainOutermostLayerIsValidation(): void
    {
        $innerBus = $this->createStub(CommandBus::class);
        $unitOfWork = $this->createStub(UnitOfWork::class);
        $validator = $this->createStub(CommandValidator::class);
        $deferredEventBus = new DeferredDomainEventBus(new FakeContainer([]));

        $result = CommandBusBuilder::from($innerBus)
            ->withEventFlushing($deferredEventBus)
            ->withTransactional($unitOfWork)
            ->withValidation($validator)
            ->build();

        self::assertInstanceOf(ValidationCommandBus::class, $result);
    }
}
