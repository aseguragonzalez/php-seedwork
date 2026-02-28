# PHP SeedWork

DDD and Hexagonal (Clean) Architecture building blocks (aggregates, entities, value
objects, command/query handlers, etc).

## Goal

- **Unify patterns:** All domain and application code extends or implements
  SeedWork abstractions, keeping the codebase consistent and predictable.
- **Keep the domain pure:** Domain types depend only on SeedWork domain types;
  no framework or infrastructure in the domain layer.
- **Clear boundaries:** Application use cases are expressed as command handlers
  (writes) and query handlers (reads), with primitives-only DTOs at the port
  boundary.

See the [docs](docs/) for architecture and usage details.

## Architecture role

SeedWork sits between project conventions and application/domain code.

### Layers

- **Domain layer:** Extends SeedWork domain bases (`AggregateRoot`, `Entity`,
  `ValueObject`), uses `EntityId`, raises `DomainEvent` and `DomainException`/
  `ValueException`, and defines repository interfaces extending `Repository`.
- **Application layer:** Use case interfaces extend `CommandHandler`
  or `QueryHandler` and implement `handle()`. Handlers
  implement those interfaces and depend on domain repository interfaces (and
  optionally `QueryRepository` for the read side).
- **Infrastructure layer:** Implements `Repository` and optionally
  `DomainEventBus` (e.g. `DeferredDomainEventBus`). Controllers dispatch to use
  cases; middleware or similar calls `DomainEventBus::publish()` after handling
  a request.

## Requirements

- PHP 8.4 or later
- Composer 2.x
- Docker and Dev Container for development

## Installation

Install the package with Composer:

```bash
composer require aseguragonzalez/php-seedwork
```

## How to use

After installation, the library is available under the `SeedWork\` namespace.

- **[Component reference](docs/component-reference.md)** — All interfaces, base
  classes, and infrastructure components.
- **[Coding standards](docs/coding-standards.md)** — Conventions and do/don't
  guidelines.
- **[Best practices](docs/best-practices.md)** — How to use the package in your
  project.
- **[tests/Fixtures/BankAccount/](tests/Fixtures/BankAccount/)** — Full
  working example (including query handlers and query repository for
  projections) about how to use the package.

Source and issue tracker: [php-seedwork](https://github.com/aseguragonzalez/php-seedwork).

## Built with

- **PHP** 8.4
- **Composer** for dependency management
- **PHPUnit** ^12.5 for tests
- **PHPStan** ^2.1 for static analysis
- **PHP-CS-Fixer** ^3.93 for code style
- **PHP_CodeSniffer** (PSR-12) for linting

## Development

If you plan to contribute, please read [CONTRIBUTING.md](CONTRIBUTING.md) and
[CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md).

**Debugging:** The PHP debug port in this project is **9000** (not 9003). Configure your IDE or Xdebug client to
connect to port 9000 when debugging tests or scripts.

From the root directory of the project (where the `Makefile` is located):

- `make install` — Install pre-commit hooks and Composer dependencies.
- `make all` — Run format-check, lint, static analysis, and tests.
- `make test` — Run PHPUnit (with coverage in `coverage/`).
- `make format` — Fix code style with PHP-CS-Fixer (PSR-12).
- `make format-check` — Check style without changing files.
- `make lint` — Run PHP_CodeSniffer (PSR-12).
- `make static-analyse` — Run PHPStan (level max).
- `make clean` — Remove vendor, coverage, and caches.
- `make create-package` — Build a zip archive in `dist/`.

## Releasing

CI checks that `VERSION` and `CHANGELOG.md` are present, well-formed, and in sync
(the version in `VERSION` must match the first versioned section in `CHANGELOG.md`).
PRs that change `src/` or `composer.json` must also update `VERSION` and/or `CHANGELOG.md`.

1. Edit `VERSION` in this directory with the new semantic version (e.g.
   `0.1.0`, `0.2.0-alpha`).
2. Commit and push to `main`, or merge a pull request.
3. The CD workflow runs on push to `main`. If the tag `v{VERSION}` does not exist,
   it runs the CI workflow, which runs checks, builds the package, and creates a
   GitHub Release with the zip artifact and tag `v{VERSION}`.
4. No manual `git tag` or `git push --tags` is required. When editing CHANGELOG
   links for new releases, use the tag format `vX.Y.Z` (e.g. `.../releases/tag/v0.1.0`).

## References

This package draws on the following literature and on the experience of building
solid, scalable, and maintainable systems in different stacks (PHP, C#, Python,
TypeScript).

- Eric Evans, *Domain-Driven Design: Tackling Complexity in the Heart
  of Software* [1]
- Vaughn Vernon, *Implementing Domain-Driven Design* [2]
- Robert C. Martin, *Clean Architecture: A Craftsman's Guide to Software Structure and Design* [3]
- .NET Microservices: Architecture for Containerized .NET Applications [4]
- Architecture Patterns with Python by Harry Percival [5]

## License

[MIT License](LICENSE). Copyright (c) 2026 Alfonso Segura.

[1]: https://www.amazon.es/Domain-Driven-Design-Tackling-Complexity-Software/dp/0321125215
[2]: https://www.amazon.es/Implementing-Domain-Driven-Design-Vaughn-Vernon/dp/0321834577
[3]: https://www.amazon.es/Clean-Architecture-Craftsmans-Software-Structure/dp/0134494164
[4]: https://learn.microsoft.com/en-us/dotnet/architecture/microservices/
[5]: https://www.oreilly.com/library/view/architecture-patterns-with/9781492052197/
