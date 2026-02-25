# Example: Cursor rules (SeedWork-based project)

Use this as a Cursor rule so the AI follows SeedWork and DDD/Clean Architecture.
Create a file such as `.cursor/rules/seedwork-ddd.mdc` in your project and paste
the **Rule content** below. You can change the `globs` if you want the rule to
apply only to certain paths.

---

## Rule file: `.cursor/rules/seedwork-ddd.mdc`

**Frontmatter (YAML):**

```yaml
---
description: DDD and Clean Architecture with SeedWork package
globs: **/*.php
---
```

**Rule content:**

```markdown
# SeedWork DDD / Clean Architecture

This project uses `aseguragonzalez/seedwork` for domain and application building blocks.

## Domain

- **Entities:** Extend `SeedWork\Domain\Entity`; identity via `EntityId` subclass; override `validate()`. Equality by id only.
- **Value objects:** Extend `SeedWork\Domain\ValueObject`; immutable (readonly); implement `equals()` by value and `validate()`.
- **Aggregates:** Extend `SeedWork\Domain\AggregateRoot`; single entry point; state changes return new instance and append events; no mutable state exposure. Reference other aggregates by id only.
- **Events:** Extend `SeedWork\Domain\DomainEvent`; past tense; EventId + type + version + serializable payload; UTC for createdAt.
- **Repositories:** Interface in domain extending `SeedWork\Domain\Repository`; implementation in infrastructure. Methods: findBy, save, deleteBy.
- **Obtainer:** Use `SeedWork\Domain\AggregateObtainer` in command handlers for "obtain by id or throw" (NotFoundResource).

## Application

- **Commands:** One class per use case extending `SeedWork\Application\Command`; one `CommandHandler`. Handler: obtain aggregate → call domain method → save → publish(collectEvents()). Prefer primitive/simple DTO attributes in Command.
- **Queries:** One class per use case extending `SeedWork\Application\Query`; one `QueryHandler` returning `QueryResult` subclass; read-only; no command dispatch or state change. Query handlers can depend on a `QueryRepository` for projection data (getById, filter); implement QueryRepository in infrastructure. Use `FilterCriteria` subclasses for `filter()` (implement `validate()` for allowed fields); keep projections as simple DTOs and map to `QueryResult` in the handler.
- **Port boundary:** Commands/Queries use primitive or simple DTO attributes; handlers convert to domain types.
- **Entry points (controllers, API):** Only map request → Command/Query, dispatch via `CommandBus`/`QueryBus`, map result to response; no domain or infrastructure in the entry point.

## Infrastructure

- Implement `Repository`, `UnitOfWork`; use `ContainerCommandBus`/`ContainerQueryBus` with PSR-11; register command/query FQCN to handler service id.
- Stack: `TransactionalCommandBus(DomainEventFlushCommandBus(ContainerCommandBus), UnitOfWork)`. Same `DeferredDomainEventBus` in handlers and in flush decorator. Prefer deferred event bus for monolithic API/MVC apps when transactionality and bounded-context isolation are desired and no message broker is used.
- Event handlers implement `DomainEventHandler`; subscribe by event FQCN on `DomainEventBus`.

## Conventions

- PHP 8.4+, `declare(strict_types=1);`, PSR-12, readonly where possible.
- Names: Command = verb; Query = GetX; Event = past tense; Handler = XxxCommandHandler / XxxQueryHandler / XxxEventHandler.

## Do

- Keep domain free of framework and infrastructure.
- One use case per command/query + one handler.
- Return new aggregate instances from behavior methods; append events.
- Use AggregateObtainer in handlers.
- Use DomainException / ValueException / NotFoundResource in domain.

## Don't

- Put business logic in command/query handlers.
- Mutate aggregate then emit events separately.
- Return domain entities from query handlers.
- Flush event bus outside the transaction.
- Reference other aggregate roots by object (use EntityId only).
```

---

## Full `.mdc` file (copy-paste)

If your Cursor version supports a single rule file, you can use this entire block
as the content of `.cursor/rules/seedwork-ddd.mdc` (including the opening `---`
and closing `---` of the frontmatter):

```markdown
---
description: DDD and Clean Architecture with SeedWork package
globs: **/*.php
---

# SeedWork DDD / Clean Architecture

This project uses `aseguragonzalez/seedwork` for domain and application building blocks.

## Domain

- **Entities:** Extend `SeedWork\Domain\Entity`; identity via `EntityId` subclass; override `validate()`. Equality by id only.
- **Value objects:** Extend `SeedWork\Domain\ValueObject`; immutable (readonly); implement `equals()` by value and `validate()`.
- **Aggregates:** Extend `SeedWork\Domain\AggregateRoot`; single entry point; state changes return new instance and append events; no mutable state exposure. Reference other aggregates by id only.
- **Events:** Extend `SeedWork\Domain\DomainEvent`; past tense; EventId + type + version + serializable payload; UTC for createdAt.
- **Repositories:** Interface in domain extending `SeedWork\Domain\Repository`; implementation in infrastructure. Methods: findBy, save, deleteBy.
- **Obtainer:** Use `SeedWork\Domain\AggregateObtainer` in command handlers for "obtain by id or throw" (NotFoundResource).

## Application

- **Commands:** One class per use case extending `SeedWork\Application\Command`; one `CommandHandler`. Handler: obtain aggregate → call domain method → save → publish(collectEvents()). Prefer primitive/simple DTO attributes in Command.
- **Queries:** One class per use case extending `SeedWork\Application\Query`; one `QueryHandler` returning `QueryResult` subclass; read-only; no command dispatch or state change. Query handlers can depend on a `QueryRepository` for projection data (getById, filter); implement QueryRepository in infrastructure. Use `FilterCriteria` subclasses for `filter()` (implement `validate()` for allowed fields); keep projections as simple DTOs and map to `QueryResult` in the handler.
- **Port boundary:** Commands/Queries use primitive or simple DTO attributes; handlers convert to domain types.
- **Entry points (controllers, API):** Only map request → Command/Query, dispatch via `CommandBus`/`QueryBus`, map result to response; no domain or infrastructure in the entry point.

## Infrastructure

- Implement `Repository`, `UnitOfWork`; use `ContainerCommandBus`/`ContainerQueryBus` with PSR-11; register command/query FQCN to handler service id.
- Stack: `TransactionalCommandBus(DomainEventFlushCommandBus(ContainerCommandBus), UnitOfWork)`. Same `DeferredDomainEventBus` in handlers and in flush decorator. Prefer deferred event bus for monolithic API/MVC apps when transactionality and bounded-context isolation are desired and no message broker is used.
- Event handlers implement `DomainEventHandler`; subscribe by event FQCN on `DomainEventBus`.

## Conventions

- PHP 8.4+, `declare(strict_types=1);`, PSR-12, readonly where possible.
- Names: Command = verb; Query = GetX; Event = past tense; Handler = XxxCommandHandler / XxxQueryHandler / XxxEventHandler.

## Do

- Keep domain free of framework and infrastructure.
- One use case per command/query + one handler.
- Return new aggregate instances from behavior methods; append events.
- Use AggregateObtainer in handlers.
- Use DomainException / ValueException / NotFoundResource in domain.

## Don't

- Put business logic in command/query handlers.
- Mutate aggregate then emit events separately.
- Return domain entities from query handlers.
- Flush event bus outside the transaction.
- Reference other aggregate roots by object (use EntityId only).
```

Customers can copy this file into their repo and adjust namespaces or package
name as needed.
