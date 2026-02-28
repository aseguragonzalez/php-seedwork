# php-seedwork

DDD and Hexagonal Architecture building blocks for PHP.
**This is a library of abstractions, not a domain application.**
Every class is extended/implemented/composed by downstream projects — design decisions here are public contracts.

## Commands

- `make test` — PHPUnit (coverage in `coverage/`)
- `make format` — PHP-CS-Fixer (PSR-12)
- `make format-check` — check only
- `make lint` — PHP_CodeSniffer (PSR-12)
- `make static-analyse` — PHPStan level max
- `make all` — format-check + lint + static analysis + tests. Run before every commit.

## Architecture

- `src/Domain/` — Entity, ValueObject, AggregateRoot, DomainEvent, EntityId, Repository, UnitOfWork,
 exceptions. **Zero external dependencies.**
- `src/Application/` — Command, Query, handlers, buses, QueryResult. **Depends only on Domain.**
- `src/Infrastructure/` — ContainerCommandBus, TransactionalCommandBus, DeferredDomainEventBus, etc.
 **Only layer that may use PSR or library types.**

Never leak Infrastructure or framework types into Domain or Application.

## Key rules

- PHP 8.4+, `declare(strict_types=1);` always.
- `final` on classes unless explicitly designed for extension; abstract base classes are `abstract`, never `final`.
- `readonly` properties and constructor promotion by default.
- Interfaces for contracts (Repository, CommandBus); abstract classes for shared behaviour (AggregateRoot, Entity).
- No `mixed` types without justification. Use PHPStan `@template`/`@extends` for generics.
- Exceptions: extend `DomainException`, `ValueException`, or `NotFoundResource`. Never bare `\Exception`.
- Backward compatibility matters: adding required params, renaming classes, or changing return types are breaking changes.

## Fixture and examples

- Canonical fixture: `tests/Fixtures/BankAccount/` — full bounded context showing how every building
 block is used. **Read this before creating new patterns.**
- When adding a new base class or interface, add a concrete implementation in the fixture.
- Update consumer examples in `docs/examples/` when changes affect downstream usage.

## Testing

- PHPUnit ^12.5, tests in `tests/`.
- Use `createMock()` to verify interactions, `createStub()` for stand-ins. Never mock domain objects — use the fixture.
- Test naming: `test{Behavior}` or `test_{snake_case_behavior}`.
- Edge cases matter more here than in app code — consumers depend on predictable behaviour.

## Code review

When reviewing changes, check in this order:

1. **Layer rules** — no upward dependency leaks.
2. **Contract compatibility** — public/protected signatures unchanged or backward-compatible. Flag breaking changes.
3. **API surface** — new public methods truly necessary? Interfaces lean (ISP)?
4. **Invariants** — validation lives in the value object / entity, not left to consumers.
5. **Tests** — happy path + edge cases for new/changed behaviour. Mock/stub choices correct.
6. **Docs** — `docs/component-reference.md` updated, fixture updated, examples updated.
7. **`make all` passes.**

## Reference docs

For coding standards, best practices, and full component reference, see `docs/`:

- `docs/coding-standards.md` — style rules and conventions
- `docs/best-practices.md` — do/don't, naming, layer rules, bus stacking
- `docs/component-reference.md` — every interface and base class
