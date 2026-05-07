<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Application\CommandBus;
use SeedWork\Application\CommandValidator;
use SeedWork\Domain\UnitOfWork;

/**
 * Fluent builder for composing a CommandBus pipeline from a base bus and
 * optional decorator layers.
 *
 * Start with any base CommandBus (e.g. {@see ContainerCommandBus}) and chain
 * decorators. Each call wraps the current bus in the next layer.
 *
 * Recommended composition order (call in this sequence so the outermost layer
 * is added last):
 *   from(base) > withEventFlushing() > withTransactional() > withValidation() > build()
 *
 * Example:
 * <code>
 * $bus = CommandBusBuilder::from($containerBus)
 *     ->withEventFlushing($deferredEventBus)
 *     ->withTransactional($unitOfWork)
 *     ->withValidation($validator)
 *     ->build();
 * </code>
 *
 * @see ContainerCommandBus        Default base bus.
 * @see DomainEventFlushCommandBus Event-flush decorator.
 * @see TransactionalCommandBus    Transaction decorator.
 * @see ValidationCommandBus       Validation decorator.
 */
final class CommandBusBuilder
{
    private function __construct(private CommandBus $commandBus)
    {
    }

    public static function from(CommandBus $commandBus): self
    {
        return new self($commandBus);
    }

    /**
     * Wraps the current bus in a {@see DomainEventFlushCommandBus}.
     */
    public function withEventFlushing(DeferredDomainEventBus $domainEventBus): self
    {
        $this->commandBus = new DomainEventFlushCommandBus($this->commandBus, $domainEventBus);
        return $this;
    }

    /**
     * Wraps the current bus in a {@see TransactionalCommandBus}.
     */
    public function withTransactional(UnitOfWork $unitOfWork): self
    {
        $this->commandBus = new TransactionalCommandBus($this->commandBus, $unitOfWork);
        return $this;
    }

    /**
     * Wraps the current bus in a {@see ValidationCommandBus}.
     */
    public function withValidation(CommandValidator $validator): self
    {
        $this->commandBus = new ValidationCommandBus($this->commandBus, $validator);
        return $this;
    }

    public function build(): CommandBus
    {
        return $this->commandBus;
    }
}
