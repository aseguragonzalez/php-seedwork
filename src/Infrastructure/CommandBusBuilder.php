<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Application\CommandBus;
use SeedWork\Application\DomainEventBus;
use SeedWork\Domain\UnitOfWork;

/**
 * Fluent builder for composing a CommandBus pipeline from a RegistryCommandBus base
 * and optional decorator layers.
 *
 * Steps are accumulated and applied in reverse order during {@see build()}, so the
 * first step added becomes the outermost decorator (the first to receive a command).
 *
 * Example — Validation outermost, events dispatched inside the transaction:
 * <code>
 * $registry = new RegistryCommandBus();
 * $registry->register(MyCommand::class, new MyCommandHandler());
 *
 * $bus = (new CommandBusBuilder($registry))
 *     ->withValidation()
 *     ->withTransactional($unitOfWork)
 *     ->withDomainEventCoordination($deferredEventBus)
 *     ->build();
 * </code>
 *
 * @see RegistryCommandBus                  Base bus; passed via constructor.
 * @see DomainEventCoordinatorCommandBus    Event-coordination decorator.
 * @see TransactionalCommandBus             Transaction decorator.
 * @see ValidationCommandBus               Validation decorator.
 */
final class CommandBusBuilder
{
    /** @var list<\Closure(CommandBus): CommandBus> */
    private array $steps = [];

    public function __construct(private readonly RegistryCommandBus $registry)
    {
    }

    public function registry(): RegistryCommandBus
    {
        return $this->registry;
    }

    /**
     * Adds a {@see DomainEventCoordinatorCommandBus} step to the pipeline.
     */
    public function withDomainEventCoordination(DomainEventBus $domainEventBus): self
    {
        $domainEventBus_ = $domainEventBus;
        $this->steps[] = fn (CommandBus $inner): CommandBus =>
            new DomainEventCoordinatorCommandBus($inner, $domainEventBus_);
        return $this;
    }

    /**
     * Adds a {@see TransactionalCommandBus} step to the pipeline.
     */
    public function withTransactional(UnitOfWork $unitOfWork): self
    {
        $unitOfWork_ = $unitOfWork;
        $this->steps[] = fn (CommandBus $inner): CommandBus =>
            new TransactionalCommandBus($inner, $unitOfWork_);
        return $this;
    }

    /**
     * Adds a {@see ValidationCommandBus} step to the pipeline.
     */
    public function withValidation(): self
    {
        $this->steps[] = fn (CommandBus $inner): CommandBus => new ValidationCommandBus($inner);
        return $this;
    }

    /**
     * Adds a custom middleware step to the pipeline.
     *
     * @param \Closure(CommandBus): CommandBus $middleware
     */
    public function use(\Closure $middleware): self
    {
        $this->steps[] = $middleware;
        return $this;
    }

    /**
     * Builds the composed CommandBus pipeline.
     *
     * Steps are applied in reverse order: the first step added wraps the outermost layer.
     */
    public function build(): CommandBus
    {
        $bus = $this->registry;
        foreach (array_reverse($this->steps) as $step) {
            $bus = $step($bus);
        }
        return $bus;
    }
}
