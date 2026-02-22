# Example: Copilot / AI instructions (SeedWork-based project)

Copy this into your project as GitHub Copilot instructions (e.g.
`.github/copilot-instructions.md`) or as general project instructions for AI
assistants. Adjust the namespace and package name if needed.

---

## Project instructions (SeedWork-based DDD / Clean Architecture)

This project uses the **SeedWork** package (`aseguragonzalez/seedwork`) for DDD
and Hexagonal Architecture.

### Domain layer

- **Entities** extend `SeedWork\Domain\Entity` and use an `EntityId` subclass
  for identity. Equality is by id only. Override `validate()` for invariants.
- **Value objects** extend `SeedWork\Domain\ValueObject`; they are immutable
  (readonly) and compared by value. Implement `equals()` and `validate()`.
- **Aggregate roots** extend `SeedWork\Domain\AggregateRoot`. State changes
  return new instances and append domain events; do not mutate in place.
  Enforce all invariants inside the aggregate.
- **Domain events** extend `SeedWork\Domain\DomainEvent`; name them in past
  tense (e.g. MoneyDeposited, OrderPlaced). Use EventId, type, version, and a
  serializable payload. Use UTC for timestamps.
- **Repositories** are interfaces in the domain extending
  `SeedWork\Domain\Repository` with a single aggregate root type.
  Implementations live in infrastructure.
- **Exceptions:** Use `SeedWork\Domain\Exceptions\DomainException`,
  `ValueException`, and `NotFoundResource` for domain errors. Do not throw
  framework or generic exceptions in domain code.

### Application layer

- **Commands:** One command per write use case (extends
  `SeedWork\Application\Command`). One handler implementing `CommandHandler`.
  Prefer primitive or simple DTO attributes in the command; handlers convert to
  domain types. Handler flow: obtain aggregate (use `AggregateObtainer`), call
  domain method(s), save, then `publish(aggregate->collectEvents())` via
  DomainEventBus. Keep handlers thin (orchestration only).
- **Queries:** One query per read use case (extends
  `SeedWork\Application\Query`). One handler implementing `QueryHandler` and
  returning a `QueryResult` subclass (DTO). No side effects in query handlers.
- **Domain event handlers:** Implement `DomainEventHandler`; register on the
  event bus by event FQCN. One concern per handler; design for idempotency if
  the bus can redeliver.
- **Entry points (controllers, API):** Only map request to Command/Query,
  dispatch via `CommandBus`/`QueryBus`, and map result to response; no domain
  or infrastructure in the entry point.

### Infrastructure

- Implement `Repository` and `UnitOfWork` in infrastructure. Use
  `ContainerCommandBus` and `ContainerQueryBus` (PSR-11) and register each
  command/query class to its handler. Wrap the command bus with
  `TransactionalCommandBus` (outside) and `DomainEventFlushCommandBus` (inside),
  so the transaction wraps the command and event flush. Use the same
  `DeferredDomainEventBus` in handlers and in the flush decorator; subscribe
  event handlers by event FQCN. Prefer the deferred event bus for monolithic
  API/MVC apps when transactionality and bounded-context isolation are
  desired and no message broker is used.

### Naming and style

- PHP 8.4+, `declare(strict_types=1);`, PSR-12. Readonly where possible. One
  main class per file.
- **Commands:** verb or verb phrase (e.g. DepositMoney, TransferMoney).
- **Queries:** GetX or GetXStatus (e.g. GetBankAccountStatus).
- **Events:** past tense (e.g. MoneyDeposited).
- **Handlers:** XxxCommandHandler, XxxQueryHandler, XxxEventHandler.

### Do / Don't (short)

- **Do:** Keep domain free of framework/infrastructure; one use case per
  command/query; return new aggregate + events from behavior methods; use
  AggregateObtainer in handlers; stack buses as Transaction → Event flush →
  Container.
- **Don't:** Put business logic in handlers; mutate aggregate then emit events
  separately; return domain entities from query handlers; flush events outside
  the transaction; reference other aggregate roots by object (use id only).
