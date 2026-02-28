# CLAUDE.md — SeedWork PHP Project

> **Template** shipped with `aseguragonzalez/seedwork`.
> Copy to your project root and adapt the **Project-specific** sections.
> Keep this file under 150 lines — shorter files produce better results.
> Move detailed or task-specific docs to separate files and reference them
> with `@path/to/file` syntax.

## Project overview

<!-- Replace this block with a one-liner about YOUR project -->
PHP 8.4 application using DDD and Hexagonal Architecture, built on the
`aseguragonzalez/seedwork` package.

## Commands

```bash
# Install
composer install

# Tests
./vendor/bin/phpunit

# Single test
./vendor/bin/phpunit --filter TestClassName

# Lint (PSR-12)
./vendor/bin/php-cs-fixer fix --dry-run --diff

# Static analysis
./vendor/bin/phpstan analyse
```

## Architecture

Three layers — Domain (pure business logic), Application (orchestration),
Infrastructure (adapters and ports). Domain must never import from Application
or Infrastructure.

```text
src/
├── Domain/<BoundedContext>/
│   ├── Entities/          # Aggregates and entities
│   ├── ValueObjects/      # Immutable value objects
│   ├── Events/            # Domain events (past tense)
│   ├── Exceptions/        # Domain exceptions
│   └── Repositories/      # Interfaces only
├── Application/<BoundedContext>/
│   └── <UseCase>/         # Interface + DTO + Handler (3 files)
└── Infrastructure/
    ├── Adapters/           # Repository implementations, mappers
    └── Ports/              # Controllers, CLI, views
tests/Unit/                 # Mirrors src/ exactly
```

## SeedWork rules

- Aggregates extend `Seedwork\Domain\AggregateRoot` — private constructor,
  static `new()` (creates + emits event) and `build()` (reconstitutes).
- Entities extend `Seedwork\Domain\Entity` — identity by id.
- Value objects extend `Seedwork\Domain\ValueObject` — `final readonly`,
  validate in constructor, throw `ValueException` on invalid state.
- Domain events extend `Seedwork\Domain\DomainEvent` — past-tense name,
  serializable payload, `new()` static constructor.
- Exceptions extend `Seedwork\Domain\Exceptions\DomainException`.
- Repository interfaces extend `Seedwork\Domain\Repository` in Domain.
  Implementations live in Infrastructure.
- Command handlers implement `Seedwork\Application\CommandHandler`.
  Query handlers implement `Seedwork\Application\QueryHandler`.

## Code style

- `declare(strict_types=1)` always.
- PSR-12. `final` classes. `readonly` properties.
- Explicit types on all parameters and return values.
- No `mixed`, no untyped parameters.
- Constructor injection only.

## Application layer rules

- Commands/Queries use **primitives only** (`string`, `int`, `bool`, `float`).
  Never use `EntityId`, value objects, or entities in DTOs.
- Handlers are thin: obtain aggregate → call domain → save → publish events.
  No business logic in handlers.
- Query handlers must have no side effects.

## Infrastructure layer rules

- Bus stacking: `TransactionalCommandBus` (outer) →
  `DomainEventFlushCommandBus` (inner) → `ContainerCommandBus`.
- Use `DeferredDomainEventBus`. Subscribe handlers by event FQCN.

## Testing

- PHPUnit. Arrange → Act → Assert.
- Use `createStub()` for return-value dependencies.
  Use `createMock()` + `expects()` only to verify interactions.
- Use Faker for test data.
- Validate: invariants, emitted events, exceptions, handler orchestration.

## Common mistakes — do not

- Put business logic in handlers.
- Mutate aggregates then emit events separately.
- Return domain entities from query handlers.
- Flush events outside the transaction.
- Reference aggregates by object instead of id.
- Use domain types in Command/Query DTOs.
- Skip `new()` / `build()` static constructors.
- Throw framework exceptions in domain code.

## Workflow

- Before creating a new class, check if SeedWork or the project already
  provides a suitable abstraction.
- For non-trivial changes, state the plan and wait for confirmation.
- Always include or update the corresponding test.
- Run `./vendor/bin/phpunit` to verify changes before finishing.

## Project-specific

<!-- ============================================================
     ADD YOUR PROJECT DETAILS BELOW.

     Suggestions:
     - Describe your bounded contexts and what each one does
     - List any custom shared kernel classes
     - Note CI/CD commands or deployment steps
     - Document gotchas or workarounds specific to your codebase
     - Reference detailed docs: @docs/api-conventions.md
     ============================================================ -->
