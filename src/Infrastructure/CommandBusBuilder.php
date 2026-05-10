<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Application\CommandBus;
use SeedWork\Domain\UnitOfWork;

/**
 * Fluent builder for composing a CommandBus pipeline from a base bus and
 * optional decorator layers.
 *
 * The default base bus is {@see RegistryCommandBus}. Start with
 * {@see CommandBusBuilder::new()} (zero-arg) or {@see CommandBusBuilder::from()}
 * with a custom base, then chain decorators.
 *
 * Recommended composition order (call in this sequence so the outermost layer
 * is added last):
 *   from(base) > withDomainEventFlushing() > withTransactional() > withValidation() > build()
 *
 * Example:
 * <code>
 * $bus = CommandBusBuilder::new()
 *     ->withDomainEventFlushing($deferredEventBus)
 *     ->withTransactional($unitOfWork)
 *     ->withValidation()
 *     ->build();
 * </code>
 *
 * @see RegistryCommandBus         Default base bus.
 * @see DomainEventFlushCommandBus Event-flush decorator.
 * @see TransactionalCommandBus    Transaction decorator.
 * @see ValidationCommandBus       Validation decorator.
 */
final class CommandBusBuilder
{
    private function __construct(private CommandBus $commandBus)
    {
    }

    /**
     * Creates a builder with a {@see RegistryCommandBus} as the base bus.
     */
    public static function new(): self
    {
        return new self(new RegistryCommandBus());
    }

    /**
     * Creates a builder with the given command bus as the base.
     */
    public static function from(CommandBus $commandBus): self
    {
        return new self($commandBus);
    }

    /**
     * Returns the inner {@see RegistryCommandBus} for handler registration.
     * Only available when the base bus is a RegistryCommandBus.
     *
     * @throws \LogicException When the base bus is not a RegistryCommandBus.
     */
    public function registry(): RegistryCommandBus
    {
        if (!$this->commandBus instanceof RegistryCommandBus) {
            throw new \LogicException('Base bus is not a RegistryCommandBus.');
        }
        return $this->commandBus;
    }

    /**
     * Wraps the current bus in a {@see DomainEventFlushCommandBus}.
     */
    public function withDomainEventFlushing(DeferredDomainEventBus $domainEventBus): self
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
    public function withValidation(): self
    {
        $this->commandBus = new ValidationCommandBus($this->commandBus);
        return $this;
    }

    public function build(): CommandBus
    {
        return $this->commandBus;
    }
}
