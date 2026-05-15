# Component Reference

All components live under the `SeedWork\` namespace (Domain, Application, Infrastructure).

## Domain layer

### AggregateRoot (`SeedWork\Domain\AggregateRoot`)

- **Role:** Root of an aggregate; single entry point for changes; records domain events.
- **Usage:** Extend with your aggregate. Implement `validate()`. State changes return a new instance and append events. Provide static factory methods (`create()`, `build()`). Annotate with `@extends AggregateRoot<YourIdType>`.
- **Key methods:** `equals(AggregateRoot $other): bool`, `getDomainEvents(): array`.
- **`$id` type:** unconstrained (`@template TId`) — use any type your bounded context prefers: plain `string`, `int`, a UUID library type, or a lightweight custom value object.

### Entity (`SeedWork\Domain\Entity`)

- **Role:** Base for DDD entities. Identity over attributes; equality by ID.
- **Usage:** Extend per entity type; implement `validate()`. Annotate with `@extends Entity<YourIdType>`.
- **Key methods:** `equals(Entity $other): bool`, `validate(): void`.
- **`$id` type:** unconstrained (`@template TId`) — same freedom as `AggregateRoot`.

### ValueObject (`SeedWork\Domain\ValueObject`)

- **Role:** Immutable object defined by attributes; equality by value.
- **Usage:** Extend; keep readonly and immutable. Implement `equals()` and `validate()`.

### DomainEvent (`SeedWork\Domain\DomainEvent`)

- **Role:** Immutable record of something that happened (past tense, e.g. `MoneyDeposited`). Carries a string id and timestamp; event-specific facts are readonly properties of the subclass.
- **Usage:** Extend; add your own readonly properties for domain-specific data. Use static factory (e.g. `create()`). Pass a unique string id (e.g. `'evt-' . uniqid()` or a UUID) to the parent constructor.
- **Key methods:** `equals(DomainEvent $other): bool` (by string id).

### Repository (`SeedWork\Domain\Repository`)

- **Role:** Collection-like interface for an aggregate root: get by id, save, delete.
- **Methods:** `save(AggregateRoot $aggregateRoot): void`, `findById(mixed $id): ?AggregateRoot`, `deleteById(mixed $id): void`.

### UnitOfWork (`SeedWork\Domain\UnitOfWork`)

- **Role:** Transaction boundary: begin, commit, rollback.
- **Methods:** `createSession(): void`, `commit(): void`, `rollback(): void`.

### Exceptions

- **DomainException** (PHP stdlib `\DomainException`): Base for domain errors. Extend to define concrete exceptions in your bounded context. No seedwork wrapper — consumers extend the stdlib class directly.

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
- **Properties:** `string $code`, `string $description`.

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

### ValidationErrorDetail / ValidationErrors (`SeedWork\Application\ValidationErrorDetail`, `SeedWork\Application\ValidationErrors`)

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
- **Usage:** `new CommandBusBuilder($registry)`, then chain `withValidation()`, `withTransaction($uow)`, `withDomainEventCoordination($eventBus)`, `use($closure)`, then `build()`. The first step added becomes the outermost decorator.
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

- **Role:** Repository decorator that publishes `$aggregate->getDomainEvents()` via `DomainEventBusPublisher` after each `save()`.
- **Usage:** Do not instantiate directly. Extend it and implement your domain repository interface so command handlers can be typed against the domain port:

```php
// Infrastructure layer of your bounded context
final class PublishingBankAccountRepository
    extends DomainEventPublishingRepository
    implements BankAccountRepository
{
    public function __construct(
        BankAccountRepository $repository,
        DomainEventBusPublisher $eventBus,
    ) {
        parent::__construct($repository, $eventBus);
    }
}
```

This is necessary because PHP's type system has no runtime generics: `DomainEventPublishingRepository` only implements the base `Repository` interface, so passing it where a domain-specific `BankAccountRepository` is expected would cause a `TypeError`. The typed subclass bridges the gap with three lines of code.

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
$repository    = new InMemoryBankAccountRepository();   // implements BankAccountRepository
$domainBus     = new DeferredDomainEventBus();
$domainBus->subscribe(AccountOpened::class, new AccountOpenedDomainEventHandler($integrationPublisher));

// Typed decorator: satisfies BankAccountRepository while adding event publishing
$publishingRepository = new PublishingBankAccountRepository($repository, $domainBus);

$registry = new RegistryCommandBus();
$registry->register(OpenAccountCommand::class,  new OpenAccountCommandHandler($publishingRepository));
$registry->register(DepositMoneyCommand::class, new DepositMoneyCommandHandler($publishingRepository));

$commandBus = (new CommandBusBuilder($registry))
    ->withValidation()
    ->withTransaction($unitOfWork)
    ->withDomainEventCoordination($domainBus)
    ->build();

// Entry point (controller, CLI, etc.)
$result = $commandBus->dispatch(new DepositMoneyCommand($accountId, 100, 'USD'));
if ($result->isFail()) {
    // handle domain rejection
}
```
