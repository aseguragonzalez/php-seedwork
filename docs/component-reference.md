# Component Reference

All components live under the `SeedWork\` namespace (Domain, Application, Infrastructure).

## Domain layer

### AggregateRoot (`SeedWork\Domain\AggregateRoot`)

- **Role:** Root of an aggregate; single entry point for changes; records domain events.
- **Usage:** Extend with your aggregate. Implement `validate()`. State changes return a new instance and append events. Provide static factory methods (`create()`, `build()`).
- **Key methods:** `equals(AggregateRoot $other): bool`, `collectEvents(): array`.

### Entity (`SeedWork\Domain\Entity`)

- **Role:** Base for DDD entities. Identity over attributes; equality by ID.
- **Usage:** Extend per entity type; implement `validate()`.
- **Key methods:** `equals(Entity $other): bool`, `validate(): void`.

### EntityId (`SeedWork\Domain\EntityId`)

- **Role:** Base for entity identifiers. One subclass per entity (e.g. `BankAccountId`).
- **Usage:** Protected constructor with `string $value`; implement `validate()`; expose static factory.
- **Key methods:** `equals(EntityId $other): bool`, `__toString(): string`.

### ValueObject (`SeedWork\Domain\ValueObject`)

- **Role:** Immutable object defined by attributes; equality by value.
- **Usage:** Extend; keep readonly and immutable. Implement `equals()` and `validate()`.

### DomainEvent (`SeedWork\Domain\DomainEvent`)

- **Role:** Immutable record of something that happened (past tense, e.g. `MoneyDeposited`). Carries identity and timestamp; event-specific facts are readonly properties of the subclass.
- **Usage:** Extend; add your own readonly properties for domain-specific data. Use static factory (e.g. `create()`).
- **Key methods:** `equals(DomainEvent $other): bool` (by EventId).

### EventId (`SeedWork\Domain\EventId`)

- **Role:** Unique identifier for a domain event (e.g. for idempotency).
- **Usage:** One subclass per event family; same pattern as EntityId.

### Repository (`SeedWork\Domain\Repository`)

- **Role:** Collection-like interface for an aggregate root: get by id, save, delete.
- **Methods:** `save(AggregateRoot $aggregateRoot): void`, `findBy(EntityId $id): ?AggregateRoot`, `deleteBy(EntityId $id): void`.

### UnitOfWork (`SeedWork\Domain\UnitOfWork`)

- **Role:** Transaction boundary: begin, commit, rollback.
- **Methods:** `createSession(): void`, `commit(): void`, `rollback(): void`.

### AggregateObtainer (`SeedWork\Domain\AggregateObtainer`)

- **Role:** Load aggregate by id or throw `NotFoundResource`.
- **Key method:** `obtain(EntityId $id): AggregateRoot`.

### Exceptions

- **DomainException** (`SeedWork\Domain\Exceptions\DomainException`): Base for domain errors.
- **ValueException** (`SeedWork\Domain\Exceptions\ValueException`): Invalid value object state.
- **NotFoundResource** (`SeedWork\Domain\Exceptions\NotFoundResource`): Aggregate/entity not found.

---

## Application layer

### Command (`SeedWork\Application\Command`)

- **Role:** Immutable DTO for a write use case. One class per use case.
- **Usage:** Extend; implement `validate(): void` to enforce preconditions.

### CommandBus (`SeedWork\Application\CommandBus`)

- **Role:** Port to dispatch commands; one handler per command type.
- **Methods:** `dispatch(Command $command): Result`.

### CommandHandler (`SeedWork\Application\CommandHandler`)

- **Role:** Use case for a write. One handler per command.
- **Usage:** Implement `handle(Command $command): void`. Orchestration only; no return value.

### Result (`SeedWork\Application\Result`)

- **Role:** Outcome of a command dispatch. Either ok or failed with one or more errors.
- **Factory methods:** `Result::ok(): Result`, `Result::failed(non-empty-array<ResultError>): Result`.
- **Methods:** `isOk(): bool`, `isFail(): bool`, `errors(): array<ResultError>`.

### ResultError (`SeedWork\Application\ResultError`)

- **Role:** A single error detail within a failed result.
- **Properties:** `string $code`, `string $message`.

### Query (`SeedWork\Application\Query`)

- **Role:** Immutable DTO for a read use case. No side effects.
- **Usage:** Extend; implement `validate(): void`.

### QueryBus (`SeedWork\Application\QueryBus`)

- **Role:** Port to dispatch queries and return a result.
- **Methods:** `ask(Query $query): Maybe`.

### QueryHandler (`SeedWork\Application\QueryHandler`)

- **Role:** Use case for a read. Returns `Maybe` wrapping the result DTO.
- **Usage:** Implement `handle(Query $query): Maybe`. Read-only.

### Maybe (`SeedWork\Application\Maybe`)

- **Role:** Represents an optional query result. Either a value (`just`) or nothing.
- **Factory methods:** `Maybe::just(mixed $value): Maybe` (null not allowed), `Maybe::nothing(): Maybe`.
- **Methods:** `hasValue(): bool`, `value(): mixed` (throws if nothing).

### DomainEventBusPublisher (`SeedWork\Application\DomainEventBusPublisher`)

- **Role:** Port to publish domain events from a repository decorator.
- **Methods:** `publish(array $events): void`.

### DomainEventBusSubscriber (`SeedWork\Application\DomainEventBusSubscriber`)

- **Role:** Port to register handlers in the composition root.
- **Methods:** `subscribe(string $eventType, DomainEventHandler $handler): void`.

### DomainEventBus (`SeedWork\Application\DomainEventBus`)

- **Role:** Full domain event bus contract: extends publisher and subscriber; adds lifecycle control.
- **Methods:** (inherits `publish()` and `subscribe()`) + `dispatch(): void`, `discard(): void`.

### DomainEventHandler (`SeedWork\Application\DomainEventHandler`)

- **Role:** React to one event type. Registered via `subscribe($eventType, $handler)`.
- **Usage:** Implement `handle(DomainEvent $event): void`. One concern per handler; idempotent.

### IntegrationEvent (`SeedWork\Application\IntegrationEvent`)

- **Role:** Contract for events published to external systems (eventual consistency via outbox).
- **Properties:** `id`, `type`, `version`, `aggregateId`, `occurredAt`, `payload`, `correlationId`, `causationId?`, `metadata?`.

### IntegrationEventPublisher (`SeedWork\Application\IntegrationEventPublisher`)

- **Role:** Port to publish integration events. Implemented in Infrastructure (outbox or in-memory spy).
- **Methods:** `publish(array $events): void`.

### IntegrationEventHandler (`SeedWork\Application\IntegrationEventHandler`)

- **Role:** Handler for incoming integration events (entry-point in the subscriber service).
- **Usage:** Implement `handle(IntegrationEvent $event): void`. One handler per event type.

### BackgroundTask (`SeedWork\Application\BackgroundTask`)

- **Role:** DTO representing a background task to be scheduled for async execution.
- **Properties:** `id`, `type`, `payload`, `correlationId`, `causationId?`, `metadata?`.

### TaskScheduler (`SeedWork\Application\TaskScheduler`)

- **Role:** Port to schedule background tasks. Implemented in Infrastructure (outbox or in-memory spy).
- **Methods:** `schedule(BackgroundTask $task): void`.

### TaskHandler (`SeedWork\Application\TaskHandler`)

- **Role:** Handler for a specific background task type.
- **Usage:** Implement `handle(BackgroundTask $task): void`. Registered by type in `InMemoryTaskScheduler`.

### ValidationError / ValidationErrors (`SeedWork\Application\ValidationError`, `SeedWork\Application\ValidationErrors`)

- **Role:** Structured validation errors thrown by `validate()` in Command/Query.

---

## Infrastructure layer

### RegistryCommandBus (`SeedWork\Infrastructure\RegistryCommandBus`)

- **Role:** In-process implementation of `CommandBus`. Resolves handler by `$command::class`.
- **Usage:** `register($commandFqcn, $handler)`, then `dispatch($command)`.

### RegistryQueryBus (`SeedWork\Infrastructure\RegistryQueryBus`)

- **Role:** In-process implementation of `QueryBus`. Resolves handler by `$query::class`.
- **Usage:** `register($queryFqcn, $handler)`, then `ask($query)`.

### CommandBusBuilder (`SeedWork\Infrastructure\CommandBusBuilder`)

- **Role:** Fluent builder for composing a `CommandBus` decorator pipeline.
- **Usage:** `new CommandBusBuilder($registry)`, then chain `withValidation()`, `withTransactional($uow)`, `withDomainEventCoordination($eventBus)`, `use($closure)`, then `build()`. The first step added becomes the outermost decorator.
- **Methods:** `registry(): RegistryCommandBus`, `build(): CommandBus`.

### QueryBusBuilder (`SeedWork\Infrastructure\QueryBusBuilder`)

- **Role:** Fluent builder for composing a `QueryBus` decorator pipeline.
- **Usage:** `new QueryBusBuilder($registry)`, then chain `withValidation()`, `use($closure)`, then `build()`.
- **Methods:** `registry(): RegistryQueryBus`, `build(): QueryBus`.

### ValidationCommandBus / ValidationQueryBus

- **Role:** Decorator that calls `validate()` on the Command/Query before forwarding. Throws `ValidationErrors` on failure.

### TransactionalCommandBus (`SeedWork\Infrastructure\TransactionalCommandBus`)

- **Role:** Decorator that wraps each command in a `UnitOfWork` (createSession → dispatch → commit or rollback).
- **Note:** Commits even on `Result::failed()` — domain rejection is not an infrastructure error.

### DomainEventCoordinatorCommandBus (`SeedWork\Infrastructure\DomainEventCoordinatorCommandBus`)

- **Role:** Decorator that coordinates the `DomainEventBus` lifecycle after each command.
  - `Result::ok()` → `eventBus->dispatch()` (run buffered handlers).
  - `Result::failed()` → `eventBus->discard()` (drop events).
  - Exception → `eventBus->discard()` then rethrow (prevent stale events leaking).
- **Usage:** Add via `CommandBusBuilder::withDomainEventCoordination($eventBus)`.

### DeferredDomainEventBus (`SeedWork\Infrastructure\DeferredDomainEventBus`)

- **Role:** Buffers domain events on `publish()`; dispatches them synchronously to subscribed handlers on `dispatch()`. Buffer is keyed by `event.id` (idempotent per-transaction).
- **Usage:** Subscribe handlers with `subscribe($eventFqcn, $handler)`. Pair with `DomainEventCoordinatorCommandBus` for automatic lifecycle management.

### DomainEventPublishingRepository (`SeedWork\Infrastructure\DomainEventPublishingRepository`)

- **Role:** Repository decorator that publishes `$aggregate->collectEvents()` via `DomainEventBusPublisher` after each `save()`.
- **Usage:** Wrap your repository; inject `DomainEventBusPublisher`. Keeps handlers unaware of event publication.

### InMemoryRepository (`SeedWork\Infrastructure\InMemoryRepository`)

- **Role:** Base for in-memory repository test doubles. Non-final; extend to add query methods or pre-seed data.
- **Usage:** Extend per aggregate type.

### IntegrationEventOutboxRecord / IntegrationEventOutboxRepository

- **Role:** Infrastructure outbox for integration events. `save()` is idempotent (keyed by `event.id`).
- **Status enum:** `IntegrationEventOutboxStatus` — `Pending`, `Published`, `Failed`.
- **Spy:** `IntegrationEventOutboxRepositorySpy` with `all()` and `reset()`.

### OutboxIntegrationEventPublisher (`SeedWork\Infrastructure\OutboxIntegrationEventPublisher`)

- **Role:** Implements `IntegrationEventPublisher` via the outbox (persists to `IntegrationEventOutboxRepository`).

### InMemoryIntegrationEventPublisher (`SeedWork\Infrastructure\InMemoryIntegrationEventPublisher`)

- **Role:** Spy implementation of `IntegrationEventPublisher` for tests. Captures events; does not execute them (integration events are for other bounded contexts).
- **Spy methods:** `published(): array`, `reset()`.

### TaskOutboxRecord / TaskOutboxRepository

- **Role:** Infrastructure outbox for background tasks. `save()` is idempotent (keyed by `task.id`).
- **Status enum:** `TaskOutboxStatus` — `Pending`, `Delivered`, `Failed`.
- **Spy:** `TaskOutboxRepositorySpy` with `all()` and `reset()`.

### OutboxTaskScheduler (`SeedWork\Infrastructure\OutboxTaskScheduler`)

- **Role:** Implements `TaskScheduler` via the outbox (persists to `TaskOutboxRepository`).

### InMemoryTaskScheduler (`SeedWork\Infrastructure\InMemoryTaskScheduler`)

- **Role:** Spy + dispatcher implementation of `TaskScheduler` for tests. Buffers tasks; executes them synchronously via `executeScheduled()`.
- **Usage:** `register($type, $handler)` at setup; call `executeScheduled()` in functional tests to simulate the worker. `reset()` clears the scheduled list (handler registrations are preserved).
- **Spy methods:** `scheduled(): array`, `reset()`.

---

## Composition example

```php
// Composition root (e.g. a service container or bootstrap file)
$registry = new RegistryCommandBus();
$registry->register(DepositMoneyCommand::class, new DepositMoneyCommandHandler($obtainer, $publishingRepository));

$commandBus = (new CommandBusBuilder($registry))
    ->withValidation()
    ->withTransactional($unitOfWork)
    ->withDomainEventCoordination($deferredEventBus)
    ->build();

// Entry point (controller, CLI, etc.)
$result = $commandBus->dispatch(new DepositMoneyCommand($accountId, 100, 'USD'));
if ($result->isFail()) {
    // handle domain rejection
}
```
