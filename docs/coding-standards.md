# Coding Standards

> **Key points reference.** PHP 8.4. Strict types everywhere. PSR-12 formatting.

---

## General PHP Baseline

- Always declare `declare(strict_types=1)` at the top of every file.
- Follow PSR-12 for formatting and file structure.
- Use `readonly` classes and constructor property promotion for immutable types (Value Objects, Commands, Queries, Events).
- Use PHP enums (`enum Foo: string`) instead of constants or string literals for fixed sets of values.
- Use `@template` PHPStan annotations for compile-time generics (PHP has no runtime generics).
- Use named arguments when calling constructors with many parameters.
- No `public` mutable properties — use `readonly` or accessor methods.

```php
<?php

declare(strict_types=1);

namespace MyService\Domain;

use SeedWork\Domain\ValueObject;

readonly class Email extends ValueObject
{
    public function __construct(
        public readonly string $value,
    ) {
        parent::__construct();
    }

    protected function validate(): void
    {
        if (! filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmail($this->value);
        }
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self && $this->value === $other->value;
    }
}
```

**Do**
- Declare `strict_types=1` in every file, no exceptions.
- Use constructor property promotion to eliminate boilerplate.
- Use `readonly` on all properties that must not change after construction.

**Don't**
- Use `array` where a typed collection or DTO is appropriate.
- Leave validation outside the constructor for value types.

---

## Do / Don't Overview

| Do | Don't |
|---|---|
| `readonly class` for immutable types | Public mutable properties |
| Constructor property promotion | Verbose `__construct` + `$this->x = $x` |
| `@template T` PHPStan annotations | Assume PHP has runtime generics |
| `Maybe` for query results | `null` returns from query handlers |
| `Result` for command outcomes | Throwing `\DomainException` past the bus boundary |
| `RegistryCommandBus` + `CommandBusBuilder` | PSR-11 container |
| `DomainEventPublishingRepository` decorator | Manual domain event publication in handlers or repositories |
| PHP enums for status/type discriminators | String constants or magic values |

---

## Domain Layer

### Entity and AggregateRoot

`Entity` is the base for any domain object with a durable identity. `AggregateRoot` extends `Entity` and acts as the consistency boundary of the aggregate cluster — all external writes must go through it.

```php
<?php

declare(strict_types=1);

namespace MyService\Domain;

use SeedWork\Domain\AggregateRoot;

abstract readonly class Account extends AggregateRoot
{
    /**
     * @param array<\SeedWork\Domain\DomainEvent> $domainEvents
     */
    protected function __construct(
        AccountId $id,
        public readonly string $ownerId,
        public readonly Money $balance,
        array $domainEvents = [],
    ) {
        parent::__construct($id, $domainEvents);
    }

    protected function validate(): void {}

    public static function open(AccountId $id, string $ownerId, Money $initialDeposit): static
    {
        return new static(
            id: $id,
            ownerId: $ownerId,
            balance: $initialDeposit,
            domainEvents: [AccountOpened::create(
                ownerId: $ownerId,
                initialBalance: $initialDeposit,
                aggregateId: (string) $id,
            )],
        );
    }

    public function deposit(Money $amount): static
    {
        if ($amount->amount <= 0) {
            throw new InvalidDepositAmount((string) $this->id);
        }

        return new static(
            id: $this->id,
            ownerId: $this->ownerId,
            balance: $this->balance->add($amount),
            domainEvents: [...$this->getDomainEvents(), MoneyDeposited::create(
                amount: $amount,
                aggregateId: (string) $this->id,
            )],
        );
    }
}
```

**Key points**
- Identity is established at construction; never reassigned.
- Behaviour methods return new instances — the PHP `readonly` class guarantees no mutation.
- Domain logic (invariants, rules) lives in the entity, not in handlers.
- Use `static` factory methods (`open`, `reconstitute`) — never expose the constructor directly to application code.
- `reconstitute` creates an entity from persisted state without emitting domain events.

**Do**
- Throw a `\DomainException` subclass when an invariant is violated.
- Pass new events in the `domainEvents` constructor argument when returning a new instance from a behaviour method.

**Don't**
- Put orchestration, I/O, or repository calls inside an entity.
- Expose raw state-mutation methods — model named behaviours instead.

---

### Value Object

A Value Object models a domain concept with no identity — two instances with the same data are interchangeable. Immutability is enforced at the language level via `readonly`; validity is enforced at construction via `validate()`.

```php
<?php

declare(strict_types=1);

namespace MyService\Domain;

use SeedWork\Domain\ValueObject;

readonly class Money extends ValueObject
{
    public function __construct(
        public readonly int $amount,
        public readonly string $currency,
    ) {
        parent::__construct();
    }

    protected function validate(): void
    {
        if ($this->amount < 0) {
            throw new NegativeAmount($this->amount);
        }
        if (empty($this->currency)) {
            throw new EmptyCurrency();
        }
    }

    public function add(Money $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new CurrencyMismatch($this->currency, $other->currency);
        }
        return new self($this->amount + $other->amount, $this->currency);
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self
            && $this->amount === $other->amount
            && $this->currency === $other->currency;
    }
}
```

**Key points**
- `readonly class` — PHP enforces immutability at the language level.
- `equals()` is structural: all fields must match.
- Validated at construction — invalid instances cannot exist.
- `validate()` is `protected` and called in the constructor; subclasses may extend it.

**Don't**
- Use a Value Object for a concept that needs to be tracked individually over time — that is an Entity.

---

### Aggregate and Domain Events

A Domain Event represents something that happened in the domain that is relevant to the business — a meaningful fact, not a technical operation. Events are always recorded by the aggregate root itself; no external code creates or injects them.

```php
<?php

declare(strict_types=1);

namespace MyService\Domain;

use SeedWork\Domain\DomainEvent;

final readonly class AccountOpened extends DomainEvent
{
    private function __construct(
        public readonly string $ownerId,
        public readonly Money $initialBalance,
        string $id,
        string $aggregateId,
        \DateTimeImmutable $occurredAt,
    ) {
        parent::__construct($id, $aggregateId, $occurredAt);
    }

    public static function create(
        string $ownerId,
        Money $initialBalance,
        string $aggregateId,
        ?string $id = null,
        ?\DateTimeImmutable $occurredAt = null,
    ): self {
        return new self(
            $ownerId,
            $initialBalance,
            $id ?? 'evt-' . uniqid('', true),
            $aggregateId,
            $occurredAt ?? new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
        );
    }
}
```

**Key points**
- Domain events are named in past tense, business language: `AccountOpened`, `MoneyDeposited`.
- `readonly class` — events are immutable facts.
- Only the aggregate root records domain events — handlers and repositories never create or inject them.
- `aggregateId` (the emitting aggregate's ID) is required on every domain event — pass it from the aggregate root.
- `id` and `occurredAt` are provided by the `DomainEvent` base; pass them through `parent::__construct`.
- Payload uses primitives or PHP value objects that can be serialized; no aggregate references.
- Processed **synchronously within the same transaction** by `DomainEventHandler` implementations.
- **Deletion is not automatically a domain event.** Use `deleteById` for technical removal with no business significance. If deletion is a meaningful domain occurrence (e.g. closing an account), model it as an aggregate behaviour (`account->close()`) that records the event — the handler then calls `save()` or `deleteById()` as appropriate. Never use `deleteById` when a domain event must be raised.

**Do**
- Keep payloads minimal and primitive.
- Use a `static create()` factory on the event class — pass event instances into the aggregate constructor's `domainEvents` argument.

**Don't**
- Embed aggregate instances or value object hierarchies in event payloads.
- Use domain events to communicate to other services — use Integration Events for that.
- Add `type`, `version`, or `correlationId` to domain events — those belong on `IntegrationEvent`.
- Create domain events outside the aggregate — handlers, repositories, and services must never instantiate events directly.

---

### Repository (Domain Port)

A Repository is a domain port — defined in the domain layer, implemented in infrastructure. Its sole concern is the persistence of aggregates: it abstracts storage behind a collection-like interface so the domain never depends on a specific technology. Only aggregate roots have repositories; child entities and value objects are persisted as part of their aggregate, never independently.

```php
<?php

declare(strict_types=1);

namespace MyService\Domain;

use SeedWork\Domain\Repository;

/**
 * @extends Repository<AccountId, Account>
 */
interface AccountRepository extends Repository
{
}
```

**Key points**
- The interface is defined in the domain layer; the concrete implementation lives in infrastructure.
- Only identity-based operations: `findById`, `save`, `deleteById`.
- Returns full aggregates, never DTOs or raw arrays.
- `findById` returns `?Aggregate` (null when not found) — let the caller decide how to handle absence.
- Use `@extends` PHPStan annotation to carry type information at analysis time.

**Don't**
- Add query methods (`findByEmail`, `findByStatus`) to the domain repository — define a read port in the application layer.
- Return partial aggregates or associative arrays from the repository.

---

### Domain Exceptions

Domain exceptions represent business rule violations — named, typed, and defined in the domain layer. Each distinct invariant gets its own class extending PHP's `\DomainException`.

```php
<?php

declare(strict_types=1);

namespace MyService\Domain;

final class AccountNotFound extends \DomainException
{
    public function __construct(string $accountId)
    {
        parent::__construct("Account '{$accountId}' was not found.");
    }
}

final class InsufficientFunds extends \DomainException
{
    public function __construct(string $accountId, int $available, int $requested)
    {
        parent::__construct(
            "Account '{$accountId}' has insufficient funds: {$available} available, {$requested} requested."
        );
    }
}
```

**Key points**
- Extend PHP's stdlib `\DomainException` — the seedwork does not provide a custom base class.
- `\DomainException` subclasses are the only correct way to signal business rule violations.
- The **class name is the machine-readable code**: `InsufficientFunds` becomes `insufficient_funds` automatically — no need to declare a code manually.
- The **constructor message is the human-readable description**: include the relevant context (IDs, amounts) so logs and API responses are self-explanatory without having to look up the code.
- Caught by `RegistryCommandBus` and converted to `Result::failed(...)` automatically — handlers never catch them.
- Infrastructure exceptions (`\PDOException`, etc.) propagate as-is — do not wrap in `\DomainException`.
- Define one subclass per distinct business rule violation — keep them in the domain layer of your service.

---

## Application Layer

### Command and CommandHandler

A `Command` expresses an intent to change state — it carries the input data and is validated on construction. A `CommandHandler` receives a guaranteed-valid command, loads or creates an aggregate, calls the domain method, and saves. No business logic lives in the handler.

```php
<?php

declare(strict_types=1);

namespace MyService\Application\OpenAccount;

use SeedWork\Application\Command;
use SeedWork\Application\ValidationErrors;
use SeedWork\Application\ValidationErrorDetail;

readonly class OpenAccountCommand extends Command
{
    public function __construct(
        public readonly string $accountId,
        public readonly string $ownerId,
        public readonly int $initialAmount,
        public readonly string $currency,
    ) {
        parent::__construct();   // triggers validate() — required
    }

    public function validate(): void
    {
        $errors = [];

        if (empty($this->accountId)) {
            $errors[] = new ValidationErrorDetail('accountId', 'Account ID is required.');
        }
        if ($this->initialAmount < 0) {
            $errors[] = new ValidationErrorDetail('initialAmount', 'Initial amount cannot be negative.');
        }

        if (!empty($errors)) {
            throw new ValidationErrors($errors);
        }
    }
}
```

```php
<?php

declare(strict_types=1);

namespace MyService\Application\OpenAccount;

use SeedWork\Application\CommandHandler;
use MyService\Domain\Account;
use MyService\Domain\AccountId;
use MyService\Domain\AccountRepository;
use MyService\Domain\Money;

final class OpenAccountHandler implements CommandHandler
{
    public function __construct(
        private readonly AccountRepository $accounts,
    ) {}

    public function handle(OpenAccountCommand $command): void
    {
        $account = Account::open(
            new AccountId($command->accountId),
            $command->ownerId,
            new Money($command->initialAmount, $command->currency),
        );

        $this->accounts->save($account);
    }
}
```

**Key points**
- The handler's sole responsibility: obtain aggregate (or create new) → call domain method → save. No business logic.
- **Do not call `publish($aggregate->getDomainEvents())`** — `DomainEventPublishingRepository` does this automatically after `save()`.
- `validate()` is called automatically in the `Command` constructor — the handler receives a guaranteed-valid command.
- The handler returns `void`. The command bus wraps the result in `Result::ok()` on success or `Result::failed(...)` on `\DomainException`.
- `ValidationErrors` propagates as an exception — it is not converted to `Result::failed()`. Capture it in a global handler at the entry point.

**Don't**
- Put conditions over domain state in the handler.
- Manually publish domain events after saving.
- Return values from `handle()`.
- Catch `ValidationErrors` in the controller — use a global exception handler.

---

### Query and QueryHandler

A `Query` expresses an intent to read data — validated on construction, always read-only. A `QueryHandler` returns a `Maybe` wrapping the result; it never loads full aggregates nor triggers side effects.

```php
<?php

declare(strict_types=1);

namespace MyService\Application\GetBalance;

use SeedWork\Application\Query;
use SeedWork\Application\ValidationErrors;
use SeedWork\Application\ValidationErrorDetail;

readonly class GetBalanceQuery extends Query
{
    public function __construct(
        public readonly string $accountId,
    ) {
        parent::__construct();   // triggers validate() — required
    }

    public function validate(): void
    {
        if (empty($this->accountId)) {
            throw new ValidationErrors([
                new ValidationErrorDetail('accountId', 'Account ID is required.'),
            ]);
        }
    }
}
```

```php
<?php

declare(strict_types=1);

namespace MyService\Application\GetBalance;

use SeedWork\Application\Maybe;
use SeedWork\Application\QueryHandler;
use MyService\Application\ReadRepositories\AccountReadRepository;

final class GetBalanceHandler implements QueryHandler
{
    public function __construct(
        private readonly AccountReadRepository $readRepository,
    ) {}

    public function handle(GetBalanceQuery $query): Maybe
    {
        $balance = $this->readRepository->findBalance($query->accountId);

        return $balance !== null
            ? Maybe::just($balance)
            : Maybe::nothing();
    }
}
```

**Key points**
- `handle()` returns `Maybe` — never `null`, never a raw value.
- Query handlers are strictly read-only. No `save()`, no command dispatching.
- Use a **read repository** (application-layer port) that returns projections/DTOs, not the domain repository.
- Return `Maybe::nothing()` for both not-found and unauthorized — do not leak resource existence.

**Don't**
- Load a full aggregate just to extract two fields.
- Trigger side effects from a query handler.
- Return domain aggregates as query results.

---

### Integration Events

An Integration Event communicates a meaningful business fact from this bounded context to the outside world — other services or bounded contexts that need to react to it. Unlike domain events (internal, synchronous), integration events cross service boundaries and are delivered asynchronously via a message broker. They carry a stable, versioned contract: once published, their schema must not break consumers.

```php
<?php

declare(strict_types=1);

namespace MyService\Application;

use SeedWork\Application\IntegrationEvent;

final readonly class AccountOpenedIntegrationEvent extends IntegrationEvent
{
    public const string TYPE = 'account.opened';
    public const string VERSION = '1';

    public function __construct(
        public readonly string $ownerId,
        public readonly int $balance,
        public readonly string $currency,
        string $aggregateId,
        string $correlationId,
        ?string $causationId = null,
    ) {
        parent::__construct(
            type: self::TYPE,
            version: self::VERSION,
            aggregateId: $aggregateId,
            payload: [
                'ownerId' => $ownerId,
                'balance' => $balance,
                'currency' => $currency,
            ],
            correlationId: $correlationId,
            causationId: $causationId,
            metadata: null,
        );
    }
}
```

Published from a `DomainEventHandler` (see below), not from the aggregate and not from the command handler.

**`IntegrationEventHandler`** is used on the consumer side (Subscriber entry point):

```php
<?php

declare(strict_types=1);

namespace MyService\Application;

use SeedWork\Application\IntegrationEventHandler;

final class AccountOpenedIntegrationEventHandler implements IntegrationEventHandler
{
    public function handle(AccountOpenedIntegrationEvent $event): void
    {
        // React to an integration event from another bounded context
    }
}
```

**Key points**
- `type` and `version` are constants on the concrete class, passed to `parent::__construct`.
- `aggregateId` identifies the source aggregate — pass it from the domain event.
- `correlationId` is mandatory on every integration event — propagate it from the execution context.
- `causationId` is the ID of the domain event that triggered this integration event.
- Version from day one: `'1'` is a version. Schema evolution adds optional fields; never rename or remove existing ones.
- Publish via `IntegrationEventPublisher` from a `DomainEventHandler` — never from the aggregate, never from the command handler.

**Don't**
- Publish integration events directly to the broker from a handler — always go through `IntegrationEventPublisher`.
- Omit `correlationId`.
- Mutate an existing integration event schema — introduce a new version instead.

---

### Background Tasks

A Background Task defers work that must happen eventually but does not need to complete within the current transaction — sending emails, triggering webhooks, calling external APIs. Tasks are written to an outbox before the transaction commits, guaranteeing at-least-once execution by an async worker even if the process crashes mid-flight.

```php
<?php

declare(strict_types=1);

namespace MyService\Application;

use SeedWork\Application\BackgroundTask;

final readonly class SendWelcomeEmailTask extends BackgroundTask
{
    public const string TYPE = 'send_welcome_email';

    public function __construct(
        public readonly string $accountId,
        public readonly string $email,
        string $correlationId,
        ?string $causationId = null,
        string $id = '',
        ?array $metadata = null,
    ) {
        parent::__construct(
            id: $id ?: \Ramsey\Uuid\Uuid::uuid4()->toString(),
            type: self::TYPE,
            payload: ['accountId' => $accountId, 'email' => $email],
            correlationId: $correlationId,
            causationId: $causationId,
            metadata: $metadata,
        );
    }
}
```

```php
<?php

declare(strict_types=1);

namespace MyService\Application;

use SeedWork\Application\TaskHandler;

final class SendWelcomeEmailTaskHandler implements TaskHandler
{
    public function handle(SendWelcomeEmailTask $task): void
    {
        // Send welcome email to $task->email
        // Must be idempotent — may be called more than once for the same task ID
    }
}
```

Scheduled from a `DomainEventHandler`:

```php
$this->taskScheduler->schedule(new SendWelcomeEmailTask(
    accountId: $event->aggregateId,
    email: $event->ownerEmail,
    correlationId: $this->requestContext->correlationId(),
    causationId: $event->id,
));
```

**Key points**
- `TYPE` is a static string constant — used by the worker to route the task to the correct `TaskHandler`.
- Schedule from a `DomainEventHandler` via `$taskScheduler->schedule($task)` — never from the aggregate.
- `TaskHandler` implementations must be **idempotent** by task ID — at-least-once execution is the delivery guarantee.
- `correlationId` is mandatory — read it from the execution context (`RequestContext`), not from the domain event.
- Set `causationId` to the domain event ID that triggered the task.

**Don't**
- Perform fire-and-forget work without going through `TaskScheduler` — tasks not in the outbox are lost on crash.
- Use background tasks to communicate with other services — use integration events for that.

---

### Execution context — correlationId propagation

`correlationId` is a cross-cutting tracing concern. It is set at the entry point and read by any component that needs it — without threading it through function signatures. The `Command` and `DomainEvent` do **not** carry `correlationId`.

In traditional synchronous PHP (one process per request), a request-scoped service is the idiomatic mechanism:

```php
<?php

declare(strict_types=1);

namespace MyService\Infrastructure;

final class RequestContext
{
    private string $correlationId = '';

    public function initialize(string $correlationId): void
    {
        $this->correlationId = $correlationId;
    }

    public function correlationId(): string
    {
        return $this->correlationId;
    }
}
```

At the entry point (controller, subscriber):

```php
// Read from HTTP header or generate a new one
$correlationId = $request->headers->get('X-Correlation-ID') ?? (string) Uuid::uuid4();
$requestContext->initialize($correlationId);
```

Inject `RequestContext` into any `DomainEventHandler` or infrastructure component that needs to propagate `correlationId`. The seedwork does not provide this abstraction — define it in your service.

> In async PHP (Swoole, FrankenPHP workers), use fiber-local storage or an equivalent mechanism to avoid cross-request leakage.

---

### DomainEventHandler (wiring Integration Events and Tasks)

A `DomainEventHandler` reacts to a domain event inside the same transaction. It is the only place where integration events are published and background tasks are scheduled — never from the aggregate or the command handler.

```php
<?php

declare(strict_types=1);

namespace MyService\Application;

use SeedWork\Application\DomainEventHandler;
use SeedWork\Application\IntegrationEventPublisher;
use SeedWork\Application\TaskScheduler;
use MyService\Infrastructure\RequestContext;

final class AccountOpenedDomainEventHandler implements DomainEventHandler
{
    public function __construct(
        private readonly IntegrationEventPublisher $integrationEventPublisher,
        private readonly TaskScheduler $taskScheduler,
        private readonly RequestContext $requestContext,
    ) {}

    public function handle(AccountOpened $event): void
    {
        $this->integrationEventPublisher->publish(
            new AccountOpenedIntegrationEvent(
                ownerId: $event->ownerId,
                balance: $event->initialBalance->amount,
                currency: $event->initialBalance->currency,
                aggregateId: $event->aggregateId,
                correlationId: $this->requestContext->correlationId(),
                causationId: $event->id,
            ),
        );

        $this->taskScheduler->schedule(
            new SendWelcomeEmailTask(
                accountId: $event->aggregateId,
                email: $event->ownerEmail,
                correlationId: $this->requestContext->correlationId(),
                causationId: $event->id,
            ),
        );
    }
}
```

**Key points**
- Inject `IntegrationEventPublisher` and `TaskScheduler` — never instantiate them in the handler.
- Both `publish()` and `schedule()` write to the outbox **inside the current transaction**. Actual delivery is eventual.
- `IntegrationEventPublisher::publish()` takes a **single** `IntegrationEvent` — call it once per event.
- Read `correlationId` from `RequestContext` — `DomainEvent` does not carry it.
- Set `causationId` to the domain event's `id`.
- Use `$event->aggregateId` (available on the `DomainEvent` base) — no need to duplicate it as a field on the event.

---

### Result and Maybe

`Result` is the return type of every command dispatch: `ok` on success, `failed` with typed errors on a business rule violation. `Maybe` is the return type of every query: `just(value)` when found, `nothing()` when absent. Both eliminate null returns and unchecked exceptions from the application boundary.

```php
// Command bus returns Result — inspect at the entry point (controller)
$result = $commandBus->dispatch($command);

if ($result->isFailed()) {
    // $result->errors() returns array<ResultError>
    return $this->errorResponse($result->errors());
}

// Query bus returns Maybe — inspect at the entry point
$maybe = $queryBus->ask($query);

if (!$maybe->hasValue()) {
    return $this->notFoundResponse();
}

$data = $maybe->value();
```

**Key points**
- `Result::ok()` — success. `Result::failed(array<ResultError> $errors)` — domain failure.
- `Maybe::just($value)` — found. `Maybe::nothing()` — absent.
- `Maybe::hasValue()` checks presence; `Maybe::value()` retrieves the value (throws if nothing).
- The command bus catches `\DomainException` and converts it to `Result::failed(...)`. Handlers never return `Result` directly.
- `ValidationErrors` propagates as an exception — not converted to `Result::failed()`. Capture it globally at the entry point.
- Infrastructure exceptions propagate as-is — not wrapped in `Result`.

---

## Infrastructure Layer

### DomainEventBus Wiring

The `DeferredDomainEventBus` is the standard implementation of `DomainEventBus`. Instantiate it once, wire subscriptions, then pass it to `DomainEventPublishingRepository` and `CommandBusBuilder`.

```php
<?php

declare(strict_types=1);

use SeedWork\Infrastructure\DeferredDomainEventBus;
use SeedWork\Infrastructure\DomainEventPublishingRepository;
use MyService\Domain\AccountOpened;
use MyService\Application\AccountOpenedDomainEventHandler;

// 1. Instantiate the bus
$domainEventBus = new DeferredDomainEventBus();

// 2. Register subscriptions (composition root)
$domainEventBus->subscribe(
    AccountOpened::class,
    new AccountOpenedDomainEventHandler(
        $integrationEventPublisher,
        $taskScheduler,
        $requestContext,
    ),
);

// 3. Wrap the domain repository with the publishing decorator.
//    Extend DomainEventPublishingRepository and implement your domain repository
//    interface so command handlers are typed against the domain port, not the decorator.
$publishingRepository = new ConcretePostgresAccountRepository(
    $pdo,
    $domainEventBus,   // satisfies DomainEventBusPublisher
);
```

**Key points**
- `DeferredDomainEventBus` buffers events by ID (idempotent) and dispatches them after the handler completes but before commit.
- Subscribe with the fully-qualified class name of the domain event (`AccountOpened::class`).
- Pass the bus as `DomainEventBusPublisher` to the repository constructor — the handler never sees it.
- Pass the bus as `DomainEventBus` to `CommandBusBuilder::withDomainEventCoordination()`.
- `DomainEventPublishingRepository` is a **concrete decorator** — extend it in your typed infrastructure class and call `parent::__construct($repository, $eventBus)`. Extending it (rather than using it directly) preserves the typed domain repository interface for command handlers.

---

### CommandBusBuilder — Full Wiring

The correct bus stack for a write operation is:

```
TransactionalCommandBus → DomainEventCoordinatorCommandBus → RegistryCommandBus
```

```php
<?php

declare(strict_types=1);

use SeedWork\Infrastructure\CommandBusBuilder;
use SeedWork\Infrastructure\RegistryCommandBus;

// 1. Build the registry and register handlers
$registry = new RegistryCommandBus();
$registry->register(OpenAccountCommand::class, new OpenAccountHandler($publishingRepository));
$registry->register(DepositMoneyCommand::class, new DepositMoneyHandler($publishingRepository));

// 2. Compose the bus pipeline
$commandBus = (new CommandBusBuilder($registry))
    ->withTransaction($unitOfWork)                     // TransactionalCommandBus (outermost)
    ->withDomainEventCoordination($domainEventBus)     // DomainEventCoordinatorCommandBus
    ->build();                                         // RegistryCommandBus (innermost)
```

**Key points**
- Always use `CommandBusBuilder` — never instantiate bus decorators manually.
- No PSR-11 container. Handlers are registered by command class name with concrete instances via `RegistryCommandBus::register()`.
- `withTransaction($unitOfWork)` must come before `withDomainEventCoordination()` — events are dispatched inside the transaction.
- `withDomainEventCoordination($domainEventBus)` calls `dispatch()` on success and `discard()` on failure.
- Command validation happens automatically in the `Command` constructor — no additional validation layer is needed.

**Don't**
- Register handlers with a PSR-11 container string — pass instances directly.
- Place `withDomainEventCoordination()` outside `withTransaction()` — events must be dispatched within the open transaction.

---

### QueryBusBuilder

`QueryBusBuilder` composes the query pipeline. Unlike the command bus, it requires no transaction or domain event coordination — query handlers are strictly read-only.

```php
use SeedWork\Infrastructure\QueryBusBuilder;
use SeedWork\Infrastructure\RegistryQueryBus;

$registry = new RegistryQueryBus();
$registry->register(GetBalanceQuery::class, new GetBalanceHandler($readRepository));

$queryBus = (new QueryBusBuilder($registry))->build();
```

**Key points**
- Query handlers must not open transactions — no `withTransaction()` on the query bus.
- Query validation happens automatically in the `Query` constructor.

---

### InMemoryRepository

Use `InMemoryRepository` from the `Testing` namespace in tests — it is a concrete class implementing the `InMemoryRepositorySpy` interface.

```php
<?php

declare(strict_types=1);

namespace MyService\Tests\Infrastructure;

use SeedWork\Testing\InMemoryRepository;
use MyService\Domain\Account;
use MyService\Domain\AccountId;
use MyService\Domain\AccountRepository;

/**
 * @extends InMemoryRepository<AccountId, Account>
 */
final class InMemoryAccountRepository extends InMemoryRepository implements AccountRepository
{
}
```

**Key points**
- Located at `SeedWork\Testing\InMemoryRepository` — not in Infrastructure.
- Implements `InMemoryRepositorySpy`: exposes `all()` and `reset()` for test assertions.
- Do not override `findById()` or `deleteById()` with narrower parameter types — the base signatures use `mixed $id`. Rely on the `@extends` PHPStan annotation for compile-time type checking.

### DeferredDomainEventBusSpy (Testing)

For tests that need to assert on buffered domain events or reset the bus between scenarios, use `DeferredDomainEventBusSpy` instead of `DeferredDomainEventBus`:

```php
use SeedWork\Testing\DeferredDomainEventBusSpy;

$domainEventBus = new DeferredDomainEventBusSpy();

// after dispatching a command:
$this->assertCount(1, $domainEventBus->pending());
$this->assertInstanceOf(AccountOpened::class, $domainEventBus->pending()[0]);

// reset between test scenarios (preserves handler subscriptions):
$domainEventBus->reset();
```

- `pending(): list<DomainEvent>` — events buffered but not yet dispatched.
- `reset()` clears the buffer without dispatching. Different from `discard()` (production lifecycle call).
- Use `DeferredDomainEventBus` in production wiring; `DeferredDomainEventBusSpy` in tests only.

---

## Naming Conventions

| Concept | Convention | Example |
|---|---|---|
| Classes, interfaces, enums | `PascalCase` | `AccountRepository`, `OpenAccountCommand` |
| Methods | `camelCase` | `findById`, `getDomainEvents`, `handle` |
| Properties | `camelCase` | `$accountId`, `$occurredAt` |
| Files | `PascalCase.php` | `OpenAccountHandler.php` |
| Namespaces | `PascalCase`, mirror directory structure | `MyService\Application\OpenAccount` |
| Constants | `UPPER_SNAKE_CASE` | `TYPE`, `VERSION` |
| Commands | `{Verb}{Noun}Command` | `OpenAccountCommand`, `DepositMoneyCommand` |
| Queries | `{Get/Find}{Noun}Query` | `GetBalanceQuery`, `FindTransactionQuery` |
| Handlers | `{CommandOrQuery}Handler` | `OpenAccountHandler`, `GetBalanceHandler` |
| Domain Events | Past tense, `PascalCase` | `AccountOpened`, `MoneyDeposited` |
| Integration Events | Past tense + suffix | `AccountOpenedIntegrationEvent` |
| Background Tasks | Imperative + suffix | `SendWelcomeEmailTask` |
| Repositories | `{Aggregate}Repository` | `AccountRepository` |
| Read Repositories | `{Aggregate}ReadRepository` | `AccountReadRepository` |
| InMemory test doubles | `InMemory{Aggregate}Repository` | `InMemoryAccountRepository` |

---

## PHPStan Annotations for Generics

PHP 8.4 has no runtime generics. Use PHPStan `@template` annotations to carry type information at static analysis time.

```php
/**
 * @template TId
 * @template TAgg of AggregateRoot<TId>
 * @extends Repository<TId, TAgg>
 */
interface AccountRepository extends Repository
{
    /** @param TId $id */
    public function findById(mixed $id): ?Account;
}
```

```php
/**
 * @template TId
 * @template TAgg of AggregateRoot<TId>
 * @implements InMemoryRepositorySpy<TId, TAgg>
 */
final class InMemoryAccountRepository extends InMemoryRepository implements AccountRepository
{
    /** @var array<string, TAgg> */
    // inherited store from InMemoryRepository
}
```

**Key points**
- Always annotate generic interfaces and base classes with `@template`.
- Use `@extends` and `@implements` to propagate type parameters in subclasses.
- PHPStan enforces these at analysis time — they have no runtime effect.
- IDs are `mixed` — use `(string) $id` to normalize before storage.
