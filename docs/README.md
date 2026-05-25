# SeedWork Documentation

This package provides DDD and Hexagonal (Clean) Architecture building blocks for
PHP applications.

## Contents

- [Architecture](architecture.md) — Standard service anatomy, outbox pattern, Unit
  of Work, idempotency, retry/DLQ, and observability (correlationId/causationId).
- [Best practices](best-practices.md) — DDD/hexagonal layer rules, component
  responsibilities, operation flow diagrams, and a decision guide for Domain Events
  vs Integration Events vs Background Tasks.
- [Component reference](component-reference.md) — Every interface, base class, and
  infrastructure component.
- [Coding standards](coding-standards.md) — Conventions aligned with the package
  and DDD/Clean Architecture, with do/don't guidelines.

## Complete code examples

A full working example that uses all SeedWork building blocks lives in:

- **[examples/BankAccount/](examples/BankAccount/)** — Domain
  (aggregate root, entities, value objects, events, repository interface, obtainer),
  application (commands, queries, command/query handlers, event handlers), and
  infrastructure (in-memory aggregate repository, in-memory query repository for
  projections). Use it as a reference when building your own bounded context.

## Quick links

- [Domain layer](component-reference.md#domain-layer) — Entities, Value Objects,
  Aggregates, Events, Repositories.
- [Application layer](component-reference.md#application-layer) — Commands,
  Queries, Buses, Event handling.
- [Infrastructure](component-reference.md#infrastructure-layer) — Container
  buses, transactions, deferred events.
