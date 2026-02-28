# Copilot Project Instructions — SeedWork (PHP 8.4+)

> **What is this file?**
> Template instructions for AI coding agents (GitHub Copilot, Claude, Cursor, etc.)
> shipped with the `aseguragonzalez/seedwork` package.
>
> **Setup:** Copy this file to `.github/copilot-instructions.md` in your project.
> Optionally, split language- or layer-specific rules into
> `.github/instructions/<name>.instructions.md` files with `applyTo` frontmatter
> (see Section 11).
>
> **Important:** Start with the rules most relevant to your project, test with
> real pull requests, and iterate. Remove sections you don't need — shorter
> instruction files produce better results.

---

## 1 · Agent Behavior

- When a requirement is ambiguous (e.g. unclear aggregate boundary or missing
  invariant), ask a clarifying question instead of guessing — wrong assumptions
  are expensive to undo.
- For non-trivial changes (new aggregate, new bounded context, refactoring),
  briefly state the planned approach and wait for confirmation before writing
  code, so the developer can correct course early.
- Before creating new classes, check whether a suitable abstraction already exists
  in SeedWork or in the project. Duplicating SeedWork functionality leads to
  divergent behavior and maintenance burden.
- Every code suggestion that adds or modifies behavior must include or update the
  corresponding unit test, because untested code is incomplete code.

---

## 2 · Architecture Overview

This project uses Domain-Driven Design within a Hexagonal Architecture
(Ports & Adapters). The domain layer must remain pure — no framework or
infrastructure imports — so it stays testable and portable.

| Principle | Rule | Why |
| --------- | ------ | ----- |
| **Language** | PHP 8.4+ · `declare(strict_types=1)` · PSR-12 | Strict types catch bugs at boundaries; PSR-12 ensures consistent formatting. |
| **Immutability** | Prefer `readonly` properties and `final` classes | Immutable objects prevent accidental state corruption across layers. |
| **One use case = one class** | Each command/query is a separate use case with its own handler | Keeps handlers focused, testable, and independently deployable. |
| **SeedWork first** | Always extend the provided base classes; never redefine them | Avoids divergent behavior and reduces maintenance burden. |

### Directory layout

```text
src/
├── Domain/<BoundedContext>/       # Pure business logic
│   ├── Entities/                  # Aggregates and entities
│   ├── ValueObjects/              # Immutable value objects
│   ├── Events/                    # Domain events (past tense)
│   ├── Exceptions/                # Domain exceptions
│   └── Repositories/              # Repository interfaces only
│
├── Application/<BoundedContext>/   # Orchestration — thin handlers
│   └── <UseCase>/
│       ├── <UseCase>.php          # Interface (extends CommandHandler|QueryHandler)
│       ├── <UseCase>Command.php   # or <UseCase>Query.php — primitives only
│       └── <UseCase>Handler.php   # Implementation
│
└── Infrastructure/                # Adapters, persistence, ports
    ├── Adapters/
    └── Ports/

tests/Unit/                        # Mirrors src/ structure exactly
```

---

## 3 · SeedWork Components

Always extend or implement these abstractions. Never redefine their behavior.

| Component | Base class / interface | Key rules |
| --------- | ---------------------- | ----------- |
| **Aggregate Root** | `SeedWork\Domain\AggregateRoot` | Private constructor + `create()` / `build()`. Emit events when something meaningful happens. Enforce all invariants inside. |
| **Entity** | `SeedWork\Domain\Entity` | Identity by `EntityId`. Equality by id. |
| **Value Object** | `SeedWork\Domain\ValueObject` | `readonly`. Compared by value. Validate in constructor. |
| **Domain Event** | `SeedWork\Domain\DomainEvent` | Past-tense name. Static constructor (no public `__construct`). Serializable payload. |
| **Domain Exception** | `SeedWork\Domain\Exceptions\DomainException` | Business-relevant message. Also: `ValueException`, `NotFoundResource`. |
| **Repository** | `SeedWork\Domain\Repository` | Interface in Domain. Implementation in Infrastructure. |
| **Command Handler** | `SeedWork\Application\CommandHandler` | Write use case. `handle(Command): void`. |
| **Query Handler** | `SeedWork\Application\QueryHandler` | Read use case. `handle(Query): TResult`. No side effects. |

---

## 4 · Domain Layer

### Aggregate Root

Use a private constructor with public static `create()` (creates and emits event)
and `build()` (reconstitutes from persistence), so construction is always valid
and event emission is never forgotten.

```php
<?php

declare(strict_types=1);

namespace Domain\Orders\Entities;

use Domain\Orders\Events\OrderCreated;
use Domain\Orders\ValueObjects\OrderDetails;
use Domain\Orders\ValueObjects\OrderEntityId;
use Seedwork\Domain\AggregateRoot;

final readonly class Order extends AggregateRoot
{
    /**
     * Constructs the order with its identity and details.
     *
     * @param OrderEntityId $id The unique identity of this order.
     * @param OrderDetails $details The details of this order.
     * @param array $domainEvents The domain events recorded by this order.
     */
    private function __construct(
        public OrderEntityId $id,
        private OrderDetails $details,
        private array $domainEvents = [],
    ) {
        parent::__construct($id, $domainEvents);
    }

    public static function create(
      OrderDetails $details,
      ?OrderEntityId $id = null
    ): self {
        $orderId = $id ?? OrderEntityId::create();
        return new self(
          id: $orderId,
          details: $details,
          domainEvents: [OrderCreated::create($orderId)],
        );
    }

    public static function build(
      OrderEntityId $id,
      OrderDetails $details,
      array $domainEvents = []
    ): self {
      return new self(
        id: $id,
        details: $details,
        domainEvents: $domainEvents,
      );
    }

    public function validate(): void
    {
        // Implement validation rules here
    }
}
```

- Reference other aggregates by id only, never by object — this prevents tight
  coupling between aggregate boundaries.
- All invariants enforced inside the aggregate or its value objects, not in handlers.

### Value Object

Validate in the constructor and throw custom `DomainException` or `ValueException` on invalid state.
Being `readonly` ensures no one can mutate the object after creation.

```php
<?php

declare(strict_types=1);

namespace Domain\Shared;

use Seedwork\Domain\ValueObject;
use Seedwork\Domain\Exceptions\ValueException;

final readonly class Email extends ValueObject
{
    public function __construct(public string $value)
    {
        parent::__construct();
    }

    public function equals(ValueObject $other): bool
    {
        if (!$other instanceof self) {
            return false;
        }

        return $this->value === $other->value;
    }

    protected function validate(): void
    {
      if (!filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
        throw new ValueException('Invalid email address');
      }
    }
}
```

### Domain Event

Name events in past tense because they represent something that already
happened. Payloads must be serializable (primitives, arrays) so events can
be stored or transmitted.

```php
<?php

declare(strict_types=1);

namespace Domain\Orders\Events;

use Domain\Orders\ValueObjects\OrderEntityId;
use Seedwork\Domain\DomainEvent;

final readonly class OrderCreated extends DomainEvent
{
    private function __construct(
        public OrderEntityId $orderId,
        OrderEventId $id,
        \DateTimeImmutable $createdAt
    ) {
      parent::__construct(
        id: $id,
        type: 'order.created',
        version: '1.0',
        payload: [
          'order_id' => $orderId->value,
        ],
        createdAt: $createdAt,
      );
    }

    public static function create(
      OrderEntityId $orderId,
      ?OrderEventId $id = null,
      ?\DateTimeImmutable $createdAt = null
    ): self {
      $eventId = $id ?? OrderEventId::create();
      $eventCreatedAt = $createdAt ?? new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
      return new self(
        orderId: $orderId,
        id: $eventId,
        createdAt: $eventCreatedAt,
      );
    }
}
```

### Repository Interface

The interface lives in Domain; the implementation lives in Infrastructure.
This inversion of dependencies keeps the domain portable.

```php
<?php

declare(strict_types=1);

namespace Domain\Orders\Repositories;

use Domain\Orders\Entities\Order;
use Seedwork\Domain\Repository;

/**
 * @extends Repository<Order>
 */
interface OrderRepository extends Repository
{
}
```

---

## 5 · Application Layer

### Use Case Interface

```php
<?php

declare(strict_types=1);

namespace Application\Orders\PlaceOrder;

use Seedwork\Application\CommandHandler;

/**
 * @extends CommandHandler<PlaceOrderCommand>
 */
interface PlaceOrder extends CommandHandler
{
}
```

### Command & Query DTOs — primitives only

Commands and Queries use only primitive types (`string`, `int`, `bool`, `float`)
and arrays of primitives. Never use domain types (`EntityId`, value objects,
entities). The handler converts primitives to domain types. This keeps the
application boundary clean and decoupled from domain internals.

```php
<?php

declare(strict_types=1);

namespace Application\Orders\PlaceOrder;

final readonly class PlaceOrderCommand extends Command
{
    public function __construct(
      public string $customerId,
      public string $productId,
      public int $quantity,
    ) {
      parent::__construct();
    }
}
```

### Handler Implementation

Keep handlers thin: obtain → call domain → save → publish events.
Business logic belongs in the aggregate or domain services, not here.

```php
<?php

declare(strict_types=1);

namespace Application\Orders\PlaceOrder;

use Domain\Orders\Entities\Order;
use Domain\Orders\Repositories\OrderRepository;
use Domain\Orders\ValueObjects\CustomerId;
use Domain\Orders\ValueObjects\OrderDetails;
use Domain\Orders\ValueObjects\ProductId;
use Domain\Orders\ValueObjects\Quantity;

final readonly class PlaceOrderHandler implements PlaceOrder
{
    public function __construct(private OrderRepository $orderRepository)
    {
    }

    public function execute(PlaceOrderCommand $command): void
    {
      $order = Order::create(
        details: new OrderDetails(
          customerId: new CustomerId($command->customerId),
          productId: new ProductId($command->productId),
          quantity: new Quantity($command->quantity),
        ),
      );

      $this->orderRepository->save($order);
    }
}
```

---

## 6 · Infrastructure Layer

- Implement `Repository` and `UnitOfWork` in infrastructure.
- Bus stacking order: `TransactionalCommandBus` (outer) →
  `DomainEventFlushCommandBus` (inner) → `ContainerCommandBus`.
  This ensures the transaction wraps (UnitOfWork) both command execution and event flush,
  so a failed event handler rolls back the entire operation.
- Use `DeferredDomainEventBus` in handlers and in the flush decorator.
- Subscribe event handlers by event FQCN on the `DomainEventBus`.

---

## 7 · Testing

- **Framework:** PHPUnit. Mirror `src/` under `tests/Unit/`.
- **Pattern:** Arrange → Act → Assert (AAA).
- **Data:** Use Faker for generating test data — avoids brittle hardcoded values.
- Use **stubs** (`createStub()` + `willReturn()`) when the test only needs a
  dependency to return a value. Use **mocks** (`createMock()` + `expects()`)
  only when verifying a method was called with specific arguments.
- Validate: domain invariants, emitted events, exception messages, handler
  orchestration.
- Avoid testing the error handling from internal dependencies only, test the error  handling from the public API.
- Test the public API of the components using real implementation from the fixture. Do not mock domain objects.
- Avoid building test that knows too much about the internal implementation of the
 components, test components as black boxes.

---

## 8 · Naming Conventions

| Element | Convention | Examples |
| ------- | ---------- | -------- |
| Aggregate / Entity | Noun | `Order`, `Restaurant` |
| Value Object | Noun phrase | `Email`, `Settings` |
| Domain Event | Past tense | `OrderPlaced`, `MoneyDeposited` |
| Domain Exception | Descriptive | `OrderNotFound`, `InsufficientFunds` |
| Command | Verb phrase | `PlaceOrder`, `DepositMoney` |
| Query | `Get` + noun | `GetOrderById`, `GetAccountStatus` |
| Handler | `<UseCase>Handler` | `PlaceOrderHandler` |
| Repository | `<Aggregate>Repository` | `OrderRepository` |

---

## 9 · Do / Don't

### ✅ Do

- Keep domain free of framework and infrastructure imports.
- One use case per command/query.
- Return new aggregate instances + events from behavior methods.
- Use `AggregateObtainer` in command handlers to fetch existing aggregates.
- Stack buses: Transaction → Event flush → Container.
- Use primitives in Command/Query DTOs.
- Write tests for every component.

### ❌ Don't

- Put business logic in handlers.
- Mutate an aggregate and then emit events separately.
- Return domain entities from query handlers to the outside world.
- Flush events outside the transaction boundary.
- Reference other aggregate roots by object (use id only as value object).
- Use domain types in Command/Query properties.
- Create aggregates without extending `AggregateRoot`.
- Skip static constructors (`create()`, `build()`).
- Use untyped or `mixed` parameters.
- Throw framework exceptions in domain code.

---

## 10 · Code Review Guidelines

When reviewing code (PRs, suggestions, or generated output), check for:

### Architecture & Layers

- Domain classes must have zero `use` statements pointing to Application or Infrastructure.
- Application handlers depend on domain interfaces, never concrete infrastructure.
- Each use case lives in its own directory with exactly three files (interface, DTO, handler).

### Domain Integrity

- Aggregates extend `AggregateRoot` with private constructor and static `create()` / `build()`.
- All invariants are enforced inside the aggregate or its value objects, not in handlers.
- Value objects are `readonly` and validate in the constructor.
- Domain events use past-tense names, `create()` constructor, and serializable payloads.
- Exceptions extend `DomainException`, `ValueException`, or `NotFoundResource`.

### Application

- Commands/Queries use only primitive types — no `EntityId`, no value objects.
- Handlers are thin: obtain → call domain → save → publish. No business logic.
- Query handlers produce no side effects.

### Style & Quality

- `declare(strict_types=1)` is present.
- Classes are `final` unless extension is explicitly needed.
- Properties are `readonly` where possible.
- PSR-12 formatting is correct.
- Explicit parameter and return types on all methods.

### Tests

- Test exists and mirrors source structure.
- Uses AAA pattern.
- Stubs for return-value dependencies; mocks only for interaction verification.
- Validates invariants, events, and exception cases.

---

## 11 · Project-specific conventions

<!--
============================================================
     ADD YOUR PROJECT CONVENTIONS BELOW THIS LINE.

     Examples of what to add here:
     - Controller conventions (routes, request/response mapping)
     - View/template conventions
     - Infrastructure adapter conventions (persistence models, mappers)
     - API response format standards
     - Authentication/authorization patterns
     ============================================================
-->
