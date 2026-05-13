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
 * $builder = CommandBusBuilder::new()
 *     ->withDomainEventCoordination($deferredEventBus)
 *     ->withTransactional($unitOfWork)
 *     ->withValidation();
 * $builder->registry()->register(MyCommand::class, new MyCommandHandler());
 * $bus = $builder->build();
 * </code>
 *
 * @see RegistryCommandBus                  Default base bus.
 * @see DomainEventCoordinatorCommandBus    Event-coordination decorator.
 * @see TransactionalCommandBus             Transaction decorator.
 * @see ValidationCommandBus               Validation decorator.
 */
final class CommandBusBuilder
{
    private ?RegistryCommandBus $registryBus;
    private CommandBus $commandBus;

    private function __construct(?RegistryCommandBus $registryBus, CommandBus $commandBus)
    {
        $this->registryBus = $registryBus;
        $this->commandBus = $commandBus;
    }

    /**
     * Creates a builder with a {@see RegistryCommandBus} as the base bus.
     */
    public static function new(): self
    {
        $registry = new RegistryCommandBus();
        return new self($registry, $registry);
    }

    /**
     * Creates a builder with the given command bus as the base.
     *
     * If the provided bus is a {@see RegistryCommandBus}, it is also used as
     * the registry accessible via {@see registry()}. Otherwise {@see registry()}
     * will throw — use {@see CommandBusBuilder::new()} when you need handler registration.
     */
    public static function from(CommandBus $commandBus): self
    {
        $registry = $commandBus instanceof RegistryCommandBus ? $commandBus : null;
        return new self($registry, $commandBus);
    }

    /**
     * Returns the inner {@see RegistryCommandBus} for handler registration.
     *
     * Always returns the same registry instance regardless of how many decorators
     * have been added, when built with {@see new()} or {@see from(RegistryCommandBus)}.
     *
     * @throws \BadMethodCallException When built via {@see from()} with a non-RegistryCommandBus base.
     */
    public function registry(): RegistryCommandBus
    {
        if ($this->registryBus === null) {
            throw new \BadMethodCallException(
                'registry() is not available when using a custom non-registry base bus via from(). Use CommandBusBuilder::new() instead.'
            );
        }
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
