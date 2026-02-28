---
description: SeedWork PHP 8.4+ DDD rules — apply to all PHP files in this project
globs: ["**/*.php", "**/tests/**/*.php"]
alwaysApply: true
---

<!--
Example: Cursor rules (SeedWork-based project)
===============================================
Use this as a Cursor rule so the AI follows SeedWork and DDD/Clean Architecture.
Create a file such as `.cursor/rules/seedwork-ddd.mdc` in your project and paste
the **Rule content** below. You can change the `globs` if you want the rule to
apply only to certain paths.
-->

# SeedWork · PHP 8.4+ · DDD + Hexagonal Architecture

## Agent Behavior

- When a requirement is ambiguous (e.g. unclear aggregate boundary or missing invariant), ask a clarifying question instead of guessing — wrong assumptions are expensive to undo.
- For non-trivial changes (new aggregate, new bounded context, refactoring), briefly state the planned approach and wait for confirmation before writing code.
- Before creating new classes, check whether a suitable abstraction already exists in SeedWork or in the project. Duplicating SeedWork functionality leads to divergent behavior.
- Every code suggestion that adds or modifies behavior must include or update the corresponding unit test.

## Architecture

This project uses **Domain-Driven Design** within a **Hexagonal Architecture (Ports & Adapters)**. The domain layer must remain pure — no framework or infrastructure imports.

```text
src/
├── Domain/<BoundedContext>/
│   ├── Entities/          # Aggregates and entities
│   ├── ValueObjects/      # Immutable value objects
│   ├── Events/            # Domain events (past tense)
│   ├── Exceptions/        # Domain exceptions
│   └── Repositories/      # Repository interfaces only
├── Application/<BoundedContext>/
│   └── <UseCase>/
│       ├── <UseCase>.php          # Interface
│       ├── <UseCase>Command.php   # or Query — primitives only
│       └── <UseCase>Handler.php   # Implementation
└── Infrastructure/
    ├── Adapters/
    └── Ports/

tests/Unit/   # Mirrors src/ exactly
````

| Rule | Why |
| ------- | ---------- | -------- |
| PHP 8.4+ · `declare(strict_types=1)` · PSR-12 | Strict types catch bugs at boundaries |
| Prefer `readonly` properties and `final` classes | Prevents accidental state mutation |
| One use case = one class | Focused, testable, independently deployable |
| Always extend SeedWork base classes | Avoids divergent behavior |

## SeedWork Components

Always extend or implement these — never redefine their behavior.

| Component | Base class | Key rules |
| ------- | ---------- | -------- |
| Aggregate Root | `SeedWork\Domain\AggregateRoot` | Private constructor + `create()` / `build()`. Emit events. Enforce invariants. |
| Entity | `SeedWork\Domain\Entity` | Identity by `EntityId`. Equality by id. |
| Value Object | `SeedWork\Domain\ValueObject` | `readonly`. Compared by value. Validate in constructor. |
| Domain Event | `SeedWork\Domain\DomainEvent` | Past-tense name. `create()` static constructor. Serializable payload. |
| Domain Exception | `SeedWork\Domain\Exceptions\DomainException` | Also: `ValueException`, `NotFoundResource`. |
| Repository | `SeedWork\Domain\Repository` | Interface in Domain. Implementation in Infrastructure. |
| Command Handler | `SeedWork\Application\CommandHandler` | Write use case. `handle(Command): void`. |
| Query Handler | `SeedWork\Application\QueryHandler` | Read use case. `handle(Query): TResult`. No side effects. |

## Domain Layer Patterns

### Aggregate Root — always use private constructor + static factories

```php
final readonly class Order extends AggregateRoot
{
    private function __construct(
        public OrderEntityId $id,
        private OrderDetails $details,
        private array $domainEvents = [],
    ) {
        parent::__construct($id, $domainEvents);
    }

    // Creates new aggregate + emits domain event
    public static function create(OrderDetails $details, ?OrderEntityId $id = null): self
    {
        $orderId = $id ?? OrderEntityId::create();
        return new self(
            id: $orderId,
            details: $details,
            domainEvents: [OrderCreated::create($orderId)],
        );
    }

    // Reconstitutes from persistence — no event emitted
    public static function build(OrderEntityId $id, OrderDetails $details, array $domainEvents = []): self
    {
        return new self(id: $id, details: $details, domainEvents: $domainEvents);
    }
}
```

- Reference other aggregates **by id only**, never by object.
- All invariants enforced inside the aggregate or its value objects, not in handlers.

### Value Object — validate in constructor, throw on invalid state

```php
final readonly class Email extends ValueObject
{
    public function __construct(public string $value)
    {
        parent::__construct();
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self && $this->value === $other->value;
    }

    protected function validate(): void
    {
        if (!filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            throw new ValueException('Invalid email address');
        }
    }
}
```

### Domain Event — past tense, serializable payload

```php
final readonly class OrderCreated extends DomainEvent
{
    private function __construct(
        public OrderEntityId $orderId,
        OrderEventId $id,
        \DateTimeImmutable $createdAt,
    ) {
        parent::__construct(
            id: $id,
            type: 'order.created',
            version: '1.0',
            payload: ['order_id' => $orderId->value],
            createdAt: $createdAt,
        );
    }

    public static function create(OrderEntityId $orderId, ?OrderEventId $id = null, ?\DateTimeImmutable $createdAt = null): self
    {
        return new self(
            orderId: $orderId,
            id: $id ?? OrderEventId::create(),
            createdAt: $createdAt ?? new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
        );
    }
}
```

### Repository Interface — Domain defines, Infrastructure implements

```php
/** @extends Repository<Order> */
interface OrderRepository extends Repository {}
```

## Application Layer Patterns

### Commands and Queries — primitives only, never domain types

```php
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

### Handlers — thin orchestrators: obtain → call domain → save → publish

```php
final readonly class PlaceOrderHandler implements PlaceOrder
{
    public function __construct(private OrderRepository $orderRepository) {}

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

## Infrastructure

- Bus stacking order: `TransactionalCommandBus` → `DomainEventFlushCommandBus` → `ContainerCommandBus`.
- Use `DeferredDomainEventBus` in handlers and in the flush decorator.
- Subscribe event handlers by event FQCN on the `DomainEventBus`.

## Testing

- Framework: **PHPUnit**. Mirror `src/` under `tests/Unit/`.
- Pattern: **Arrange → Act → Assert**.
- Use **Faker** for generating test data — avoids brittle hardcoded values.
- Use **stubs** (`createStub()` + `willReturn()`) for return-value dependencies.
- Use **mocks** (`createMock()` + `expects()`) only when verifying a method was called with specific arguments.
- Test public API only — treat components as black boxes.
- Do not mock domain objects — use real implementations from fixtures.
- Validate: domain invariants, emitted events, exception messages, handler orchestration.

## Naming Conventions

| Element | Convention | Examples |
| ------- | ---------- | -------- |
| Aggregate / Entity | Noun | `Order`, `Restaurant` |
| Value Object | Noun phrase | `Email`, `OrderDetails` |
| Domain Event | Past tense | `OrderPlaced`, `MoneyDeposited` |
| Domain Exception | Descriptive | `OrderNotFound`, `InsufficientFunds` |
| Command | Verb phrase | `PlaceOrder`, `DepositMoney` |
| Query | `Get` + noun | `GetOrderById`, `GetAccountStatus` |
| Handler | `<UseCase>Handler` | `PlaceOrderHandler` |
| Repository | `<Aggregate>Repository` | `OrderRepository` |

## ✅ Do

- Keep domain free of framework and infrastructure imports
- One use case per command/query
- Use primitives in Command/Query DTOs
- Stack buses: Transaction → Event flush → Container
- Write tests for every component
- Use `AggregateObtainer` in handlers to fetch existing aggregates

## ❌ Don't

- Put business logic in handlers
- Mutate an aggregate and emit events separately
- Return domain entities from query handlers to the outside world
- Flush events outside the transaction boundary
- Reference other aggregate roots by object — use id only
- Use domain types in Command/Query properties
- Create aggregates without extending `AggregateRoot`
- Skip static constructors (`create()`, `build()`)
- Use untyped or `mixed` parameters
- Throw framework exceptions in domain code
