<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Application\CommandBus;
use SeedWork\Application\DomainEventBus;
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
 *   from(base) > withDomainEventCoordination() > withTransactional() > withValidation() > build()
 *
 * Example:
 * <code>
 * $bus = CommandBusBuilder::new()
 *     ->withDomainEventCoordination($deferredEventBus)
 *     ->withTransactional($unitOfWork)
 *     ->withValidation()
 *     ->build();
 * $bus->registry()->register(MyCommand::class, new MyCommandHandler());
 * </code>
 *
 * @see RegistryCommandBus                  Default base bus.
 * @see DomainEventCoordinatorCommandBus    Event-coordination decorator.
 * @see TransactionalCommandBus             Transaction decorator.
 * @see ValidationCommandBus               Validation decorator.
 */
final class CommandBusBuilder
{
    private readonly RegistryCommandBus $registryBus;
    private CommandBus $commandBus;

    public function __construct()
    {
        $this->registryBus = new RegistryCommandBus();
        $this->commandBus = $this->registryBus;
    }

    /**
     * Creates a builder with a {@see RegistryCommandBus} as the base bus.
     */
    public static function new(): self
    {
        return new self();
    }

    /**
     * Creates a builder with the given command bus as the base.
     * Note: {@see registry()} is not available when using a custom base.
     *
     * @deprecated Use {@see CommandBusBuilder::new()} for the default RegistryCommandBus base.
     */
    public static function from(CommandBus $commandBus): self
    {
        $builder = new self();
        $builder->commandBus = $commandBus;
        return $builder;
    }

    /**
     * Returns the inner {@see RegistryCommandBus} for handler registration.
     *
     * When built with {@see new()}, always returns the same registry instance
     * regardless of how many decorators have been added. When built with
     * {@see from()}, returns the internal registry (not the custom base).
     */
    public function registry(): RegistryCommandBus
    {
        return $this->registryBus;
    }

    /**
     * Wraps the current bus in a {@see DomainEventCoordinatorCommandBus}.
     *
     * @param DomainEventBus $domainEventBus The event bus to coordinate.
     */
    public function withDomainEventCoordination(DomainEventBus $domainEventBus): self
    {
        $this->commandBus = new DomainEventCoordinatorCommandBus($this->commandBus, $domainEventBus);
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
