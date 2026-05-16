# Copilot instructions — php-seedwork

This repository is the **php-seedwork** package: DDD and Hexagonal (Clean) Architecture **building blocks**
for PHP. We provide base classes, interfaces, and lightweight infrastructure (aggregates, entities, value
objects, command/query buses, event bus, etc.) that downstream projects compose into their domain and
application layers.

**We are building a library of abstractions, not a domain application.** Every class we ship is meant to be
extended, implemented, or composed by consumers. Design decisions here ripple into every project that
depends on this package.

## Architecture

- **Domain** (`SeedWork\Domain\*`): Entity, ValueObject, AggregateRoot, DomainEvent, Repository, UnitOfWork.
- **Application** (`SeedWork\Application\*`): Command/CommandBus/CommandHandler, Query/QueryBus/QueryHandler,
  DomainEventBus/DomainEventHandler, IntegrationEvent/IntegrationEventPublisher/IntegrationEventHandler,
  BackgroundTask/TaskScheduler/TaskHandler, Result/Maybe/ResultError, ValidationErrors/ValidationErrorDetail.
- **Infrastructure** (`SeedWork\Infrastructure\*`): RegistryCommandBus/QueryBus, CommandBusBuilder/QueryBusBuilder,
  TransactionalCommandBus, ValidationCommandBus/QueryBus, DomainEventCoordinatorCommandBus,
  DeferredDomainEventBus, DomainEventPublishingRepository, OutboxIntegrationEventPublisher,
  OutboxTaskScheduler, IntegrationEventOutboxRepository, TaskOutboxRepository.
- **Testing** (`SeedWork\Testing\*`): Spy interfaces (DomainEventBusSpy, InMemoryRepositorySpy,
  IntegrationEventPublisherSpy, etc.) and InMemory/fake implementations (InMemoryRepository,
  DeferredDomainEventBusSpy, InMemoryIntegrationEventPublisher, InMemoryTaskScheduler, etc.).
  For use in consumer tests only — not in production code.

See [README](../README.md) and [docs/](../docs/) for the full picture.

## Layer rules

1. **Domain → nothing.** No imports from Application, Infrastructure, or any external library/framework.
2. **Application → Domain only.**
3. **Infrastructure → Application + Domain.** Only layer allowed to depend on PSR interfaces or libraries.
4. These rules apply to the package itself. Downstream consumers wire Infrastructure adapters on their own.

## Design principles for building blocks

Because every component is a public contract consumed by other projects:

- **Minimal surface area.** Expose only what consumers need; keep internals `private` or `protected`.
  Fewer public methods = fewer things we can break.
- **Favor interfaces over base classes** when the building block defines a *contract*
  (e.g. `Repository`, `CommandBus`). Use abstract base classes when sharing *behaviour*
  (e.g. `AggregateRoot` recording events, `Entity` equality).
- **Open for extension, closed for modification.** Mark classes `final` unless they are explicitly designed
  to be extended by consumers. Abstract base classes should be `abstract`, never `final`.
- **No framework coupling in Domain or Application.** If Infrastructure needs a PSR or library type,
  that's fine — but never leak it upward.
- **Immutability by default.** Use `readonly` properties and constructor promotion. Value objects must be
  immutable. Entities mutate only through guarded methods.
- **Backward compatibility matters.** Adding a required parameter to a public/protected method, renaming
  a class, or changing a return type is a breaking change. Prefer additive changes (new optional
  parameters with defaults, new interfaces, new classes).

## How we build code

- **Sources:** [src/](../src/) under `SeedWork\` namespace; one main class per file; file name = class name.
- **Specs:** [docs/coding-standards.md](../docs/coding-standards.md) is the source of truth
  (do/don't, naming, layer rules, bus stacking).
- **Reference:** [docs/component-reference.md](../docs/component-reference.md) for every interface and
  base class.
- **Conventions:** PHP 8.4+, `declare(strict_types=1);`, PSR-12, readonly where possible.
- **Typing:** Strict parameter types, return types, no `mixed` unless truly unavoidable.
  Use union types or generics via PHPStan annotations (`@template`, `@extends`) when appropriate.
- **Exceptions:** Extend `\DomainException` (PHP stdlib) for domain-specific exceptions. Never throw bare `\Exception` or `\RuntimeException`. The seedwork does not ship its own `DomainException` wrapper.

## Examples and fixtures

Update examples and fixtures each time you add or change a pattern.

- **Canonical example:** [docs/examples/BankAccount/](../docs/examples/BankAccount/) — full bounded
  context: domain (aggregate, entities, value objects, events, repository interface),
  application (commands, queries, handlers, event handlers), infrastructure (in-memory repository).
  **Always consult this fixture** before creating new patterns — follow its structure and naming.
- When adding a new base class or interface, add a concrete implementation in the fixture that
  demonstrates the intended usage.

## Testing

- **Framework:** PHPUnit ^12.5. Tests live under [tests/](../tests/) (e.g. `Tests\Domain\*`,
  `Tests\Infrastructure\*`).
- **What we test:** the public API of each building block — constructors, factory methods, and the
  behaviours they expose. We use the BankAccount fixture as the concrete implementation.
- **Prefer mocks and stubs** over real infrastructure or heavy setup:
  - **Mocks (`createMock`):** Verify interactions (e.g. handler received this command, event bus published
    these events).
  - **Stubs (`createStub`):** Stand-ins that return values or satisfy a type without asserting calls.
- **Do not mock domain objects** — use real implementations from the fixture.
- **Test naming:** `test{Behavior}` or `test_{snake_case_behavior}` — describe *what* is verified.
- **Edge cases matter more here** than in application code — consumers rely on our contracts behaving
  predictably for nulls, empty strings, boundary values, etc.

## Code review guidelines

When reviewing a PR (or self-reviewing before pushing), verify:

### Correctness

- Does the change respect the **layer rules** above? No upward dependency leaks.
- Are **invariants enforced** in the right place? (e.g. value validation inside the value object, not
  left to the consumer.)
- Does the new code work correctly with the **existing fixture**? If not, is the fixture updated?

### Contract & compatibility

- Is any **public or protected signature** changed? If so, is it backward-compatible? Flag breaking
  changes explicitly.
- Are new public methods **necessary**? Could the same goal be achieved without widening the API surface?
- Do interfaces remain **lean**? Avoid "fat" interfaces — prefer composition or separate interfaces
  (Interface Segregation).

### Quality & style

- `declare(strict_types=1);` present.
- `readonly` and `final` used appropriately.
- No `mixed` types without justification.
- PSR-12 formatting. No commented-out code or `TODO` without a linked issue.
- PHPStan level max passes (`make static-analyse`).

### Tests

- Are there **tests for the new or changed behaviour**? Check both happy path and edge cases.
- Are mock/stub choices correct? (Mock for interaction verification, stub for stand-ins.)
- Does `make all` pass?

### Documentation

- [docs/component-reference.md](../docs/component-reference.md) updated if a new interface or base class
  is added.
- Fixture updated if a new pattern is introduced.
- Consumer-facing examples updated if the change affects how downstream projects use the package.

## Tooling

- `make test` — Run PHPUnit (coverage in `coverage/`).
- `make format` / `make format-check` — PHP-CS-Fixer (PSR-12).
- `make lint` — PHP_CodeSniffer (PSR-12).
- `make static-analyse` — PHPStan (level max).
- `make all` — format-check, lint, static analysis, and tests.

## Pre-commit workflow

**Always** run both before committing:
1. `make all` — validates format, lint, static analysis, and tests.
2. `pre-commit run --all-files` — enforces JSON/YAML formatting, trailing whitespace,
   no-commit-to-main, and re-runs PHP checks.
   The conventional commit message hook runs at the `commit-msg` stage only; it is
   not triggered by `--all-files`. It is enforced automatically when you run `git commit`.

The project ships a `.pre-commit-config.yaml` that hooks format-check, phpcs, and
PHPStan into every `git commit`. Running `make all` first avoids surprises at hook time.
