# SeedWork Documentation

This package provides DDD and Hexagonal (Clean) Architecture building blocks for
PHP applications.

## Contents

- [Component reference](component-reference.md) — Every interface, base class, and
  infrastructure component.
- [Coding standards](coding-standards.md) — Conventions aligned with the package
  and DDD/Clean Architecture, with do/don't guidelines.
- [Best practices](best-practices.md) — How to use the package effectively in your
  project.

## Complete code examples

A full working example that uses all SeedWork building blocks lives in the test
suite:

- **[tests/Fixtures/BankAccount/](../tests/Fixtures/BankAccount/)** — Domain
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

## Examples for your project

- [Copilot instructions](examples/copilot-instructions.md) — Example instructions
  for GitHub Copilot.
- [Cursor rules](examples/cursor-rules.md) — Example Cursor rule to use in your
  project (e.g. `.cursor/rules/seedwork-ddd.mdc`).
- [CLAUDE instructions](examples/claude-example.md) — Example instructions
  for Claude.
