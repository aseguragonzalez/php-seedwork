# PHP SeedWork

DDD and Hexagonal (Clean) Architecture building blocks for PHP — aggregates,
entities, value objects, command/query handlers, domain events, and more.

## Goal

- **Unify patterns:** All domain and application code extends or implements
  SeedWork abstractions, keeping the codebase consistent and predictable.
- **Keep the domain pure:** Domain types depend only on SeedWork domain types;
  no framework or infrastructure in the domain layer.
- **Clear boundaries:** Application use cases are expressed as command handlers
  (writes) and query handlers (reads), with primitives-only DTOs at the port
  boundary.

## Installation

```bash
composer require aseguragonzalez/php-seedwork
```

Requires **PHP 8.4** or later.

## Documentation

- [Getting Started](getting-started.md) — Core building blocks walkthrough.
- [Architecture](architecture.md) — Service anatomy, outbox pattern, Unit of Work,
  idempotency, retry/DLQ, and observability.
- [Best Practices](best-practices.md) — Layer rules, component responsibilities,
  operation flow diagrams, and event-type decision guide.
- [Component Reference](component-reference.md) — Every interface, base class, and
  infrastructure component.
- [Coding Standards](coding-standards.md) — Conventions and do/don't guidelines.

## Example

A full working example is in
[`docs/examples/BankAccount/`](https://github.com/aseguragonzalez/php-seedwork/tree/main/docs/examples/BankAccount)
— domain, application, and infrastructure layers using every SeedWork building block.

## License

[MIT License](https://github.com/aseguragonzalez/php-seedwork/blob/main/LICENSE).
Copyright (c) 2026 Alfonso Segura.
