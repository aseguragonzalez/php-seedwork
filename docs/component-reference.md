# Component Reference

All components live under the `SeedWork\` namespace (Domain, Application,
Infrastructure).

## Domain layer

### AggregateRoot (`SeedWork\Domain\AggregateRoot`)

- **Role:** Root of an aggregate; single entry point for changes; records domain
  events.
- **Usage:** Extend with your aggregate (e.g. `BankAccount`). Constructor:
  `EntityId $id`, optional `array $domainEvents`. Implement `validate()`. State
  changes return a new instance and append events; do not mutate. Provide a static
  factory method to create a new instance (e.g. `create()` and `build()`). The
  `validate()` method should be implemented to enforce the aggregate invariants.
- **Key methods:** `equals(AggregateRoot $other): bool`, `collectEvents(): array`
  (returns copies of recorded events).

### Entity (`SeedWork\Domain\Entity`)

- **Role:** Base for DDD entities. Identity over attributes; equality by ID.
- **Usage:** Extend per entity type. Constructor receives `EntityId $id`; call
  `parent::__construct($id)` and implement `validate()`. Provide a static
  factory method to create a new instance (e.g. `create()` and `build()`). The
  `validate()` method should be implemented to enforce the entity invariants.
- **Key methods:** `equals(Entity $other): bool` (by id), `validate(): void`
  (override).

### EntityId (`SeedWork\Domain\EntityId`)

- **Role:** Base for entity identifiers. One subclass per entity (e.g.
  `BankAccountId`).
- **Usage:** Protected constructor with `string $value`; implement `validate()`
  (e.g. UUID format). Expose `fromString(string)` or `create()` for callers.
- **Key methods:** `equals(EntityId $other): bool`, `__toString(): string`.

### ValueObject (`SeedWork\Domain\ValueObject`)

- **Role:** Immutable object defined by attributes; equality by value.
- **Usage:** Extend; keep readonly and immutable. Implement
  `equals(ValueObject $other): bool` and `validate(): void`. Call
  `parent::__construct()` from subclass constructor. The `validate()` method
  should be implemented to enforce the value object invariants.
- **Key methods:** `equals()`, `validate()`.

### DomainEvent (`SeedWork\Domain\DomainEvent`)

- **Role:** Immutable record of something that happened (past tense). Carries
  type, version, payload, and `EventId`.
- **Usage:** Subclass with `EventId $id`, `string $type`, `string $version`,
  `array $payload`, `\DateTimeImmutable $createdAt`. Prefer static factory
  (e.g. `create()`) and serializable payload.
- **Key methods:** `equals(DomainEvent $other): bool` (by EventId).

### EventId (`SeedWork\Domain\EventId`)

- **Role:** Unique identifier for a domain event (e.g. for idempotency).
- **Usage:** One subclass per event family; same pattern as EntityId (protected
  constructor, `validate()`, static factory).

### Repository (`SeedWork\Domain\Repository`)

- **Role:** Collection-like interface for an aggregate root: get by id, save,
  delete.
- **Usage:** Interface in domain; extend with `@template T of AggregateRoot` and
  type-hint `T` in methods. Implementation in infrastructure.
- **Methods:** `save(AggregateRoot $aggregateRoot): void`,
  `findBy(EntityId $id): ?AggregateRoot`, `deleteBy(EntityId $id): void`.

### UnitOfWork (`SeedWork\Domain\UnitOfWork`)

- **Role:** Transaction boundary: begin, commit, rollback.
- **Usage:** Implement in infrastructure (e.g. DB transaction). Used by
  `TransactionalCommandBus`.
- **Methods:** `createSession(): void`, `commit(): void`, `rollback(): void`.

### AggregateObtainer (`SeedWork\Domain\AggregateObtainer`)

- **Role:** Load aggregate by id or throw. Avoids repeated "find + null check" in
  handlers.
- **Usage:** Extend per aggregate; inject `Repository<T>` and resource name.
  Handlers use `obtain(EntityId $id): AggregateRoot` and get `NotFoundResource`
  when missing.
- **Key method:** `obtain(EntityId $id): AggregateRoot`.

### Exceptions

- **DomainException** (`SeedWork\Domain\Exceptions\DomainException`): Base for
  domain errors.
- **ValueException** (`SeedWork\Domain\Exceptions\ValueException`): Invalid value
  object state.
- **NotFoundResource** (`SeedWork\Domain\Exceptions\NotFoundResource`):
  Aggregate/entity not found (message includes resource name and optional id).

---

## Application layer

### Command (`SeedWork\Application\Command`)

- **Role:** Immutable DTO for a write use case. One class per use case.
- **Usage:** Extend; use primitive or simple DTO attributes for port
  compatibility (or domain IDs/value objects if you accept the trade-off). Call
  `parent::__construct()`.

### CommandBus (`SeedWork\Application\CommandBus`)

- **Role:** Port to dispatch commands; one handler per command type.
- **Methods:** `dispatch(Command $command): void`.

### CommandHandler (`SeedWork\Application\CommandHandler`)

- **Role:** Use case for a write. One handler per command.
- **Usage:** Implement `handle(Command $command): void`. Depend on repositories,
  obtainers, etc; keep orchestration only.

### Query (`SeedWork\Application\Query`)

- **Role:** Immutable DTO for a read use case. No side effects.
- **Usage:** Same as Command: primitives/simple DTOs preferred at the port
  boundary.

### QueryBus (`SeedWork\Application\QueryBus`)

- **Role:** Port to dispatch queries and return a result.
- **Methods:** `ask(Query $query): QueryResult`.

### QueryHandler (`SeedWork\Application\QueryHandler`)

- **Role:** Use case for a read. Returns a single result DTO.
- **Usage:** Implement `handle(Query $query): QueryResult`. Read-only; return
  `QueryResult` subclasses, not domain entities.

### QueryResult (`SeedWork\Application\QueryResult`)

- **Role:** Immutable DTO returned by query handlers. Serializable.
- **Usage:** Extend with public readonly properties; primitives or simple
  structures.

### DomainEventBus (`SeedWork\Application\DomainEventBus`)

- **Role:** Port to publish and subscribe to domain events.
- **Methods:** `publish(array $events): void`,
  `subscribe(string $eventType, string $domainEventHandler): void`. Event type =
  event FQCN.

### DomainEventHandler (`SeedWork\Application\DomainEventHandler`)

- **Role:** React to one event type. Registered via
  `subscribe($eventType, $handlerFqcn)`.
- **Usage:** Implement `handle(DomainEvent $event): void`. One concern per
  handler; idempotent if bus is async.

### Using the application ports from an entry point

Entry points (HTTP controllers, API handlers, CLI commands) should stay thin:
no domain or infrastructure logic; only map the incoming request to a Command
or Query, call the bus, then map the result to the response.

**Write path (command):** Build a Command from the request (e.g. IDs, amounts
from request body or route), inject `CommandBus`, dispatch, then return
success/redirect/ID as needed. Let the handler and transaction deal with
persistence and events.

**Read path (query):** Build a Query from the request (e.g. resource id from
route), inject `QueryBus`, call `ask($query)`, then map the returned
`QueryResult` (or your subclass) to JSON, view data, or response DTO.

Example (framework-agnostic; names align with the BankAccount fixture):

```php
final readonly class BankAccountController
{
    public function __construct(
        private CommandBus $commandBus,
        private QueryBus $queryBus
    ) {}

    public function deposit(string $accountId, string $amount, string $currency): void
    {
        $command = new DepositMoneyCommand(
            BankAccountId::fromString($accountId),
            new Money((int) $amount, Currency::from($currency))
        );
        $this->commandBus->dispatch($command);
        // Framework integration code would handle the HTTP response (e.g., 204, redirect, or resource ID).
    }

    public function getStatus(string $accountId): BankAccountStatusResult
    {
        $query = new GetBankAccountStatusQuery(BankAccountId::fromString($accountId));
        /** @var BankAccountStatusResult $result */
        return $this->queryBus->ask($query);
    }
}
```

The controller depends only on the application ports (`CommandBus`, `QueryBus`),
the Command/Query/Result DTOs, and simple domain types used to construct those
messages; it does not depend on repositories, the domain event bus, unit of work,
or other infrastructure implementations.

---

## Infrastructure layer

### ContainerCommandBus (`SeedWork\Infrastructure\ContainerCommandBus`)

- **Role:** PSR-11 implementation of `CommandBus`. Resolves handler by
  `$command::class`.
- **Usage:** Construct with `ContainerInterface` and optional
  `commandFqcn => handlerServiceId` map; call `register($commandFqcn,
  $handlerId)`; then `dispatch($command)`.

### ContainerQueryBus (`SeedWork\Infrastructure\ContainerQueryBus`)

- **Role:** PSR-11 implementation of `QueryBus`. Resolves handler by
  `$query::class`.
- **Usage:** Same pattern as ContainerCommandBus: `register($queryFqcn,
  $handlerId)`, `ask($query)`.

### TransactionalCommandBus (`SeedWork\Infrastructure\TransactionalCommandBus`)

- **Role:** Decorator that runs each command inside a unit of work (createSession
  → dispatch → commit or rollback).
- **Usage:** Wrap your command bus and inject `UnitOfWork`. Put it outside
  `DomainEventFlushCommandBus` so the transaction wraps the command and event
  flush.

### DeferredDomainEventBus (`SeedWork\Infrastructure\DeferredDomainEventBus`)

- **Role:** Buffers events on `publish()`; dispatches to subscribed handlers only
  on `flush()`.
- **Usage:** Same container and handlers as used by command handlers. Subscribe
  with `subscribe($eventFqcn, $handlerServiceId)`. Call `flush()` after the
  command (e.g. via `DomainEventFlushCommandBus`). Recommended for monolithic
  applications when you need transactionality and isolation between bounded
  contexts and are not using a message broker (e.g. typical API or MVC
  request/response). See [Best practices](best-practices.md) for when to use
  the deferred bus.

### DomainEventFlushCommandBus (`SeedWork\Infrastructure\DomainEventFlushCommandBus`)

- **Role:** After each successful command dispatch, calls
  `DeferredDomainEventBus::flush()`.
- **Usage:** Wrap the inner CommandBus and inject the same
  `DeferredDomainEventBus` used by handlers. Stack order:
  `TransactionalCommandBus(DomainEventFlushCommandBus(ContainerCommandBus),
  UnitOfWork)`.
