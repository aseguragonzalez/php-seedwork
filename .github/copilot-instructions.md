# Copilot instructions — php-seedwork

This repository is the **php-seedwork** package: DDD and Hexagonal (Clean) Architecture **building blocks**
for PHP (aggregates, entities, value objects, command/query handlers, event bus, etc.). The goal is to help
developers build **scalable, maintainable** software based on DDD, Clean Architecture, and best practices.
SeedWork sits between project conventions and application/domain code; the **domain layer stays pure**
(no framework or infrastructure in domain).

## Architecture

- **Domain** (`SeedWork\Domain\*`): Entity, ValueObject, AggregateRoot, DomainEvent, EntityId, EventId,
  Repository, UnitOfWork, AggregateObtainer, DomainException / ValueException / NotFoundResource.
- **Application** (`SeedWork\Application\*`): Command, CommandBus, CommandHandler, Query, QueryBus,
  QueryHandler, QueryResult, DomainEventBus, DomainEventHandler.
- **Infrastructure** (`SeedWork\Infrastructure\*`): ContainerCommandBus, ContainerQueryBus,
  TransactionalCommandBus, DeferredDomainEventBus, DomainEventFlushCommandBus.

See [README](../README.md) and [docs/](../docs/) for the full picture.

## How we build code

- **Sources:** [src/](../src/) under `SeedWork\` namespace; one main class per file; file name = class name.
- **Specs:** [docs/coding-standards.md](../docs/coding-standards.md) and
  [docs/best-practices.md](../docs/best-practices.md) are the source of truth
  (do/don't, naming, layer rules, bus stacking).
- **Reference:** [docs/component-reference.md](../docs/component-reference.md) for every interface and
  base class.
- **Conventions:** PHP 8.4+, `declare(strict_types=1);`, PSR-12, readonly where possible.

## Examples and fixtures

You should update the examples and fixtures each time you add or change a pattern.

- **Canonical example:** [tests/Fixtures/BankAccount/](../tests/Fixtures/BankAccount/) — full bounded
  context: domain (aggregate, entities, value objects, events, repository interface, obtainer),
  application (commands, queries, handlers, event handlers), infrastructure (in-memory repository).
  Use it as the reference when adding or changing patterns.
- **Consumer-facing examples:** [docs/examples/copilot-instructions.md](../docs/examples/copilot-instructions.md)
  (for downstream projects) and [docs/examples/cursor-rules.md](../docs/examples/cursor-rules.md).

## Testing (mocks and stubs)

- **Framework:** PHPUnit ^12.5. Tests live under [tests/](../tests/) (e.g. `Tests\Domain\*`,
  `Tests\Infrastructure\*`).
- **Prefer mocks and stubs** instead of real infrastructure or heavy setup where possible:
  - **Mocks (`createMock`):** Use when you need to **verify interactions** (e.g. handler received this
    command, event bus published these events). Example: `$handler = $this->createMock(CommandHandler::class);`
    then `$handler->expects($this->once())->method('handle')->with($command);`.
  - **Stubs (`createStub`):** Use when you only need a **stand-in that returns a value** or satisfies a type
    (no need to assert on calls). Example: `$handler = $this->createStub(QueryHandler::class);` with
    `$handler->method('handle')->willReturn($stubResult);`.
- **Domain / Infrastructure / application-layer tests:** Test public API of the components using real
  implementation from the fixture. Do not mock domain objects.

## Tooling

- `make test` — Run PHPUnit (coverage in `coverage/`).
- `make format` / `make format-check` — PHP-CS-Fixer (PSR-12).
- `make lint` — PHP_CodeSniffer (PSR-12).
- `make static-analyse` — PHPStan (level max).
- `make all` — format-check, lint, static analysis, and tests.
