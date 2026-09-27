<?php

declare(strict_types=1);

namespace Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use SeedWork\Application\CommandBus;
use SeedWork\Application\CommandHandler;
use SeedWork\Application\DomainEventBus;
use SeedWork\Application\DomainEventHandler;
use SeedWork\Application\Result;
use SeedWork\Domain\DomainEvent;
use SeedWork\Domain\UnitOfWork;
use SeedWork\Infrastructure\CommandBusBuilder;
use SeedWork\Infrastructure\DeferredDomainEventBus;
use SeedWork\Infrastructure\DomainEventCoordinatorCommandBus;
use SeedWork\Infrastructure\RegistryCommandBus;
use SeedWork\Infrastructure\TransactionalCommandBus;
use Tests\Fixtures\TestAggregate;
use Tests\Fixtures\TestCommand;
use Tests\Fixtures\TestDomainException;
use Tests\Fixtures\TestEvent;
use Tests\Fixtures\TestRepository;

/**
 * @internal
 *
 * @coversNothing
 */
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
        ;

        self::assertSame($registry, $builder->registry());
    }

    public function testWithTransactionalProducesTransactionalCommandBus(): void
    {
        $unitOfWork = $this->createStub(UnitOfWork::class);

        $result = (new CommandBusBuilder(new RegistryCommandBus()))
            ->withTransaction($unitOfWork)
            ->build()
        ;

        self::assertInstanceOf(TransactionalCommandBus::class, $result);
    }

    public function testWithDomainEventCoordinationProducesDomainEventCoordinatorCommandBus(): void
    {
        $result = (new CommandBusBuilder(new RegistryCommandBus()))
            ->withDomainEventCoordination(new DeferredDomainEventBus())
            ->build()
        ;

        self::assertInstanceOf(DomainEventCoordinatorCommandBus::class, $result);
    }

    public function testWithDomainEventCoordinationAcceptsDomainEventBusInterface(): void
    {
        $eventBus = $this->createStub(DomainEventBus::class);

        $result = (new CommandBusBuilder(new RegistryCommandBus()))
            ->withDomainEventCoordination($eventBus)
            ->build()
        ;

        self::assertInstanceOf(DomainEventCoordinatorCommandBus::class, $result);
    }

    public function testFirstStepAddedBecomesOutermostDecorator(): void
    {
        $unitOfWork = $this->createStub(UnitOfWork::class);
        $deferredEventBus = new DeferredDomainEventBus();

        $result = (new CommandBusBuilder(new RegistryCommandBus()))
            ->withTransaction($unitOfWork)
            ->withDomainEventCoordination($deferredEventBus)
            ->build()
        ;

        self::assertInstanceOf(TransactionalCommandBus::class, $result);
    }

    public function testUseAppliesCustomMiddleware(): void
    {
        $customBus = $this->createStub(CommandBus::class);
        $customBus->method('dispatch')->willReturn(Result::ok());

        $result = (new CommandBusBuilder(new RegistryCommandBus()))
            ->use(fn (CommandBus $inner): CommandBus => $customBus)
            ->build()
        ;

        self::assertSame($customBus, $result);
    }

    public function testUseCanBeChainedWithNamedDecorators(): void
    {
        $unitOfWork = $this->createStub(UnitOfWork::class);
        $customWrapper = $this->createStub(CommandBus::class);
        $customWrapper->method('dispatch')->willReturn(Result::ok());

        $result = (new CommandBusBuilder(new RegistryCommandBus()))
            ->withTransaction($unitOfWork)
            ->use(fn (CommandBus $inner): CommandBus => $customWrapper)
            ->build()
        ;

        self::assertInstanceOf(TransactionalCommandBus::class, $result);
    }

    public function testFullStackRollsBackAndDiscardsEventsWhenHandlerWritesThenThrowsDomainException(): void
    {
        $repository = new TestRepository();
        $unitOfWork = $this->createMock(UnitOfWork::class);
        $unitOfWork->expects($this->once())->method('createSession');
        $unitOfWork->expects($this->never())->method('commit');
        $unitOfWork->expects($this->once())->method('rollback');

        $eventHandler = $this->createMock(DomainEventHandler::class);
        $eventHandler->expects($this->never())->method('handle');

        $bus = $this->buildFullStack(
            $repository,
            $unitOfWork,
            $eventHandler,
            static function (): void {
                throw new TestDomainException('Rejected after save.');
            },
        );

        $result = $bus->dispatch(new TestCommand());

        self::assertTrue($result->isFailed());
        self::assertSame('test_domain_exception', $result->errors()[0]->code);
        self::assertSame('Rejected after save.', $result->errors()[0]->description);
        self::assertCount(1, $repository->all(), 'The handler wrote before throwing; the rollback is what discards it.');
    }

    public function testFullStackCommitsAndDispatchesEventsWhenHandlerSucceeds(): void
    {
        $repository = new TestRepository();
        $unitOfWork = $this->createMock(UnitOfWork::class);
        $unitOfWork->expects($this->once())->method('createSession');
        $unitOfWork->expects($this->once())->method('commit');
        $unitOfWork->expects($this->never())->method('rollback');

        $eventHandler = $this->createMock(DomainEventHandler::class);
        $eventHandler->expects($this->once())->method('handle');

        $bus = $this->buildFullStack($repository, $unitOfWork, $eventHandler, static function (): void {});

        $result = $bus->dispatch(new TestCommand());

        self::assertTrue($result->isOk());
    }

    public function testFullStackRollsBackDiscardsEventsAndRethrowsWhenHandlerThrowsNonDomainException(): void
    {
        $repository = new TestRepository();
        $unitOfWork = $this->createMock(UnitOfWork::class);
        $unitOfWork->expects($this->once())->method('createSession');
        $unitOfWork->expects($this->never())->method('commit');
        $unitOfWork->expects($this->once())->method('rollback');

        $eventHandler = $this->createMock(DomainEventHandler::class);
        $eventHandler->expects($this->never())->method('handle');

        $bus = $this->buildFullStack(
            $repository,
            $unitOfWork,
            $eventHandler,
            static function (): void {
                throw new \RuntimeException('Storage failure.');
            },
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Storage failure.');

        $bus->dispatch(new TestCommand());
    }

    /**
     * Builds TransactionalCommandBus > DomainEventCoordinatorCommandBus > RegistryCommandBus
     * with a TestCommand handler that saves an aggregate to $repository, publishes its
     * TestEvent to the deferred event bus, then runs $afterSave.
     *
     * @param DomainEventHandler<DomainEvent> $eventHandler
     * @param \Closure(): void                $afterSave
     */
    private function buildFullStack(
        TestRepository $repository,
        UnitOfWork $unitOfWork,
        DomainEventHandler $eventHandler,
        \Closure $afterSave,
    ): CommandBus {
        $eventBus = new DeferredDomainEventBus();
        $eventBus->subscribe(TestEvent::class, $eventHandler);

        $commandHandler = $this->createStub(CommandHandler::class);
        $commandHandler->method('handle')->willReturnCallback(
            static function () use ($repository, $eventBus, $afterSave): void {
                $aggregate = TestAggregate::create()->withEvent(TestEvent::create());
                $repository->save($aggregate);
                $eventBus->publish($aggregate->getDomainEvents());
                $afterSave();
            },
        );

        $registry = new RegistryCommandBus();
        $registry->register(TestCommand::class, $commandHandler);

        return (new CommandBusBuilder($registry))
            ->withTransaction($unitOfWork)
            ->withDomainEventCoordination($eventBus)
            ->build()
        ;
    }
}
