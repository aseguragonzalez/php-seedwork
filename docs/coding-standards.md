# Coding Standards

These standards align with the SeedWork package, DDD, and Clean Architecture. Use
them as the default for new code and when refactoring.

## General

- **PHP:** 8.4 or later. Use `declare(strict_types=1);` in every PHP file.
- **Style:** PSR-12. Use the project's PHP-CS-Fixer config when available.
- **Types:** Prefer readonly classes and typed properties; avoid mutable global
  state.

---

## Do and Don't — Overview

| Do | Don't |
| --- | --- |
| Keep domain free of framework and infrastructure | Import framework or DB types in domain |
| One use case = one command/query + one handler | Put multiple use cases in one handler |
| Return new aggregate instances from behavior methods | Mutate aggregate state in place and then emit events |
| Use AggregateObtainer in handlers for "load or throw" | Repeat findBy + null check in every handler |
| Use domain exceptions (DomainException, ValueException, NotFoundResource) | Throw generic \Exception or framework exceptions in domain |
| Name events in past tense (MoneyDeposited, OrderPlaced) | Name events as commands (DepositMoney, PlaceOrder) |
| Keep aggregates small and focused | Reference full aggregates from other aggregates |
| Prefer primitives/simple DTOs at command/query boundary | Leak complex domain types through ports when avoidable |
| Stack buses: Transaction → Event flush → Container bus | Flush events outside the transaction or in wrong order |
| One main class per file; file name = class name | Put multiple unrelated classes in one file |

---

## Domain layer

### Entities

- **Do:**
  - Extend `SeedWork\Domain\Entity`
  - Use a dedicated `EntityId` subclass per entity type
  - Implement `validate()` for invariants
  - Base equality only on identity
  - Provide a static factory method to create a new instance (e.g. `create()` and `build()`)
- **Don't:**
  - Expose mutable setters that bypass invariants
  - Compare entities by attributes instead of id
  - Put infrastructure or application types in the domain

### Value objects

- **Do:**
  - Extend `SeedWork\Domain\ValueObject`
  - Keep them immutable (readonly)
  - Implement `equals()` by comparing all significant attributes
  - Implement `validate()` and call it from the constructor
  - Use static factories or named constructors if needed
- **Don't:**
  - Add identity or mutable state
  - Use value objects as entities
  - Skip validation in constructors

### Aggregates

- **Do:**
  - Have one aggregate root per consistency boundary
  - Extend `SeedWork\Domain\AggregateRoot`
  - Enforce all invariants inside the aggregate
  - Return new instances from behavior methods and append domain events
  - Expose only the root to the outside (other aggregates hold only the root's id)
  - Provide a static factory method to create a new instance (e.g. `create()` and `build()`)
- **Don't:**
  - Allow external code to modify aggregate internals
  - Hold references to other aggregate roots (use IDs)
  - Mutate state and then add events in a second step
  - Expose internal entities for direct modification

### Domain events

- **Do:**
  - Name events in past tense
  - Make them immutable
  - Include `EventId`, type, version, and a serializable payload
  - Use UTC for `createdAt`
  - Record events when something meaningful happens in the aggregate
  - Provide a static factory method to create a new instance (e.g. `create()` and `build()`)
- **Don't:**
  - Put non-serializable objects in the payload
  - Use event names that sound like commands
  - Forget to pass events through when creating new aggregate instances after a state change

### Repositories

- **Do:**
  - Define repository interfaces in the domain extending `SeedWork\Domain\Repository` with a single aggregate root type
  - Implement them in infrastructure
  - Use `findBy`, `save`, `deleteBy` only
- **Don't:**
  - Put repository implementations in the domain
  - Add query methods that return DTOs or leak persistence details
  - Expose infrastructure types in the interface

### Exceptions

- **Do:**
  - Use `SeedWork\Domain\Exceptions\DomainException` (and subclasses `ValueException`, `NotFoundResource`) for domain failures
  - Use clear, domain-oriented messages
- **Don't:**
  - Throw generic `\Exception` or framework-specific exceptions in domain code
  - Catch infrastructure exceptions in the domain layer

---

## Application layer

### Commands and command handlers

- **Do:**
  - One command class per write use case extending `SeedWork\Application\Command`
  - One handler implementing `CommandHandler`
  - Use primitives or simple DTOs in commands when possible
  - In the handler: obtain aggregate (e.g. via AggregateObtainer), call domain methods, save, then `publish(aggregate->collectEvents())`
  - Keep handlers thin (orchestration only)
- **Don't:**
  - Put business logic in the handler
  - Dispatch commands from inside another command handler without a clear reason
  - Forget to publish collected events after save
  - Use one handler for multiple command types

### Queries and query handlers

- **Do:**
  - One query class per read use case extending `SeedWork\Application\Query`
  - One handler implementing `QueryHandler` and returning a `QueryResult` subclass
  - Keep queries and results with primitive or simple DTO attributes when possible
  - Make query handlers read-only (no state changes, no command dispatch)
- **Don't:**
  - Return domain entities from query handlers
  - Mutate state or dispatch commands in a query handler
  - Reuse one query class for unrelated read use cases

### Domain event handlers

- **Do:**
  - Implement `DomainEventHandler`
  - Subscribe by event FQCN on the event bus
  - One concern per handler (e.g. update read model, send notification)
  - Design for idempotency when the bus may redeliver events
- **Don't:**
  - Put multiple unrelated side effects in one handler
  - Assume events are delivered exactly once if the bus is async
  - Depend on order of execution of other handlers

---

## Infrastructure layer

- **Do:**
  - Implement `Repository` and `UnitOfWork` in infrastructure
  - Use `ContainerCommandBus` and `ContainerQueryBus` with PSR-11
  - Wrap the command bus with `TransactionalCommandBus` (outside) and
    `DomainEventFlushCommandBus` (inside) so the transaction wraps the command and event flush
  - Register handlers with the same `DeferredDomainEventBus` that command handlers use
  - Prefer `DeferredDomainEventBus` in monoliths when you need transactionality
    and bounded-context isolation and are not using a message broker (see
    [best-practices](best-practices.md))
- **Don't:**
  - Flush the event bus outside the transaction when events must be consistent with the write
  - Put domain or application use-case logic in infrastructure
  - Depend on the domain on infrastructure

---

## Naming and layout

### Namespaces

- **Do:**
  - Use clear layer names: `…\Domain\` (Entities, ValueObjects, Events, Repositories, Exceptions),
    `…\Application\<UseCase>\` (Command, CommandHandler, Query, QueryHandler, QueryResult),
    `…\Infrastructure\` (implementations)
- **Don't:**
  - Mix layers in one namespace
  - Put application use cases in the domain namespace

### Naming conventions

- **Do:**
  - Commands: verb or verb phrase (e.g. `DepositMoney`, `TransferMoney`)
  - Queries: verb + noun (e.g. `GetBankAccountStatus`)
  - Events: past tense (e.g. `MoneyDeposited`, `OrderPlaced`)
  - Handlers: `XxxCommandHandler`, `XxxQueryHandler`, `XxxEventHandler`
  - Repositories: `XxxRepository`
  - IDs: `XxxId` (entity), `XxxEventId` (event)
- **Don't:**
  - Use command-like names for events
  - Use vague names like `ProcessData` or `HandleRequest` for commands
  - Use generic names with suffixes like `Service`, `Manager`, etc.

### Files and structure

- **Do:**
  - One main class per file
  - Match file name to class name
  - Group by feature/use case in application (e.g. `DepositMoney/DepositMoneyCommand.php`, `DepositMoney/DepositMoneyCommandHandler.php`)
- **Don't:**
  - Put multiple public classes in one file
  - Use inconsistent naming between file and class

---

## Component-specific rules (summary)

| Component | Do | Don't |
| --- | --- | --- |
| **Entity** | Identity via EntityId; override `validate()` | Compare by attributes; mutable setters |
| **ValueObject** | Immutable; `equals()` by value; `validate()` | Identity; mutability |
| **AggregateRoot** | Return new instance + events; single entry point | Mutate and emit separately; expose internals |
| **DomainEvent** | Past tense; EventId; serializable payload; UTC | Command-like names; non-serializable payload |
| **Repository** | Interface in domain; implementation in infra | Implementation in domain; rich query API in interface |
| **Command** | One per use case; primitives/simple DTOs | Business logic; multiple intents |
| **CommandHandler** | Obtain → domain → save → publish events | Business logic; skip event publish |
| **Query** | One per read use case; no side effects | State changes; command dispatch |
| **QueryHandler** | Return QueryResult DTO; read-only | Return entities; mutate state |
| **DomainEventHandler** | One concern; idempotent when async | Many concerns; assume exactly-once |

These do/don't notes and the component reference together define the coding
standard for projects using this package.
