# Getting Started — php-seedwork

This guide walks you through the core building blocks of `php-seedwork`, using a simplified bank-account scenario. A complete working example is in [`docs/examples/BankAccount/`](examples/BankAccount/).

## Installation

```bash
composer require aseguragonzalez/php-seedwork
```

Requires **PHP 8.4** or later.

---

## 1. Creating a Value Object

Value objects are immutable and equal by value, not identity. Extend `ValueObject`, add `readonly` properties, and implement `equals()` and `validate()`.

```php
use SeedWork\Domain\Exceptions\DomainException;
use SeedWork\Domain\ValueObject;

final readonly class Money extends ValueObject
{
    public function __construct(
        public int $amount,
        public string $currency,
    ) {
        parent::__construct(); // triggers validate()
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self
            && $this->amount === $other->amount
            && $this->currency === $other->currency;
    }

    protected function validate(): void
    {
        if ($this->amount <= 0) {
            throw new DomainException('Amount must be greater than 0');
        }
    }
}
```

---

## 2. Creating a Domain Event

Domain events are immutable records of something that happened. Extend `DomainEvent` and add readonly properties for the domain-specific facts. Use a private constructor and a static factory (`create()`).

```php
use SeedWork\Domain\DomainEvent;

final readonly class MoneyDeposited extends DomainEvent
{
    private function __construct(
        public string $accountId,
        public int $amount,
        string $id,
        \DateTimeImmutable $createdAt,
    ) {
        parent::__construct($id, $createdAt);
    }

    public static function create(
        string $accountId,
        int $amount,
        ?string $id = null,
        ?\DateTimeImmutable $createdAt = null,
    ): self {
        return new self(
            $accountId,
            $amount,
            $id ?? 'evt-' . uniqid('', true),
            $createdAt ?? new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
        );
    }
}
```

Key rules:
- Name events in past tense (`MoneyDeposited`, not `DepositMoney`).
- Use UTC for `createdAt`.
- `equals()` is inherited and compares by string `id`.

---

## 3. Creating an Aggregate Root

Aggregates enforce invariants and raise domain events. Extend `AggregateRoot`, keep state immutable (return a new instance from every state-change method), and record events by passing them to the constructor.

```php
use SeedWork\Domain\AggregateRoot;

final readonly class BankAccount extends AggregateRoot
{
    private function __construct(
        BankAccountId $id,
        private int $balance,
        array $domainEvents = [],
    ) {
        parent::__construct($id, $domainEvents);
    }

    public static function open(BankAccountId $id, int $initialBalance = 0): self
    {
        return new self($id, $initialBalance, [AccountOpened::create($id, $initialBalance)]);
    }

    public function deposit(int $amount): self
    {
        $event = MoneyDeposited::create($this->id->value, $amount);
        return new self($this->id, $this->balance + $amount, [...$this->getDomainEvents(), $event]);
    }

    public function getBalance(): int
    {
        return $this->balance;
    }

    protected function validate(): void {}
}
```

---

## 4. Defining the Repository (interface)

Repositories belong to the domain layer. Define an interface per aggregate that extends `Repository<T>`. Implementations live in the infrastructure layer.

```php
use SeedWork\Domain\Repository;

/**
 * @extends Repository<BankAccount>
 */
interface BankAccountRepository extends Repository {}
```

The `Repository<T>` interface declares:
- `save(AggregateRoot $aggregateRoot): void`
- `findById(mixed $id): ?AggregateRoot`
- `deleteById(mixed $id): void`

---

## 5. Creating a Command and Handler

Commands are immutable DTOs for write use cases. Handlers orchestrate the domain and return `void`; the bus wraps them in `Result`.

```php
use SeedWork\Application\Command;
use SeedWork\Application\ValidationError;
use SeedWork\Application\ValidationErrors;

final readonly class DepositMoneyCommand extends Command
{
    public function __construct(
        public string $accountId,
        public int $amount,
        public string $currency,
    ) {
        parent::__construct();
    }

    public function validate(): void
    {
        $errors = [];
        if (empty($this->accountId)) {
            $errors[] = new ValidationError('accountId', 'Account ID is required.');
        }
        if ($this->amount <= 0) {
            $errors[] = new ValidationError('amount', 'Amount must be positive.');
        }
        if (count($errors) > 0) {
            throw new ValidationErrors($errors);
        }
    }
}
```

```php
use SeedWork\Application\Command;
use SeedWork\Application\CommandHandler;

final readonly class DepositMoneyCommandHandler implements CommandHandler
{
    public function __construct(
        private Repository $repository,
    ) {}

    public function handle(Command $command): void
    {
        /** @var DepositMoneyCommand $command */
        $accountId = BankAccountId::fromString($command->accountId);
        $account = $this->repository->findById($accountId)
            ?? throw new BankAccountNotFoundException("BankAccount '{$accountId}' not found");

        $updated = $account->deposit($command->amount);
        $this->repository->save($updated);
    }
}
```

---

## 6. Creating a Query and Handler

Queries are immutable DTOs for read use cases. Handlers return `Maybe<T>` — either a value (`Maybe::just($result)`) or nothing (`Maybe::nothing()`).

```php
use SeedWork\Application\Query;
use SeedWork\Application\ValidationError;
use SeedWork\Application\ValidationErrors;

final readonly class GetAccountBalanceQuery extends Query
{
    public function __construct(public string $accountId)
    {
        parent::__construct();
    }

    public function validate(): void
    {
        if (empty($this->accountId)) {
            throw new ValidationErrors([new ValidationError('accountId', 'Account ID is required.')]);
        }
    }
}
```

```php
use SeedWork\Application\Maybe;
use SeedWork\Application\Query;
use SeedWork\Application\QueryHandler;

final readonly class GetAccountBalanceQueryHandler implements QueryHandler
{
    public function __construct(private BankAccountRepository $repository) {}

    public function handle(Query $query): Maybe
    {
        /** @var GetAccountBalanceQuery $query */
        $account = $this->repository->findById(BankAccountId::fromString($query->accountId));
        if ($account === null) {
            return Maybe::nothing();
        }
        return Maybe::just(new AccountBalanceResult($account->id->value, $account->getBalance()));
    }
}
```

---

## 7. Wiring the Bus with CommandBusBuilder

Use `CommandBusBuilder` to compose a `CommandBus` pipeline without any DI container (no PSR-11). Decorators are applied outermost-first: validation runs before the transaction, which runs before event coordination.

```php
use SeedWork\Infrastructure\CommandBusBuilder;
use SeedWork\Infrastructure\DeferredDomainEventBus;
use SeedWork\Infrastructure\RegistryCommandBus;

$domainEventBus = new DeferredDomainEventBus();

$registry = new RegistryCommandBus();
$registry->register(DepositMoneyCommand::class, new DepositMoneyCommandHandler($publishingRepository));

$commandBus = (new CommandBusBuilder($registry))
    ->withValidation()                               // outermost: validates before anything
    ->withTransactional($unitOfWork)                 // wraps in DB transaction
    ->withDomainEventCoordination($domainEventBus)   // dispatches or discards events after command
    ->build();

$result = $commandBus->dispatch(new DepositMoneyCommand($accountId, 100, 'USD'));
if ($result->isOk()) {
    // success
} elseif ($result->isFail()) {
    foreach ($result->errors() as $error) {
        echo $error->code . ': ' . $error->message;
    }
}
```

The full stack (outermost → innermost):
```
ValidationCommandBus → TransactionalCommandBus → DomainEventCoordinatorCommandBus → RegistryCommandBus
```

> **Note:** `withTransactional()` requires a `UnitOfWork` implementation (e.g. a Doctrine wrapper). Omit it when there is no database transaction boundary (e.g. in tests).

---

## 8. Publishing Domain Events with DomainEventPublishingRepository

Wrap any `Repository` with `DomainEventPublishingRepository` to automatically publish domain events after every `save()`. The handler stays unaware of event publication.

```php
use SeedWork\Infrastructure\DeferredDomainEventBus;
use SeedWork\Infrastructure\DomainEventPublishingRepository;

$domainEventBus = new DeferredDomainEventBus();

// Subscribe handlers before wiring
$domainEventBus->subscribe(
    AccountOpened::class,
    new AccountOpenedDomainEventHandler($integrationEventPublisher)
);

// Wrap the repository — publish happens automatically after save()
$publishingRepository = new DomainEventPublishingRepository($repository, $domainEventBus);
```

`DeferredDomainEventBus` buffers events keyed by `event.id` (idempotent). The `DomainEventCoordinatorCommandBus` calls `dispatch()` on success or `discard()` on failure/exception.

---

## 9. Running it

A minimal composition root that ties everything together:

```php
use SeedWork\Infrastructure\CommandBusBuilder;
use SeedWork\Infrastructure\DeferredDomainEventBus;
use SeedWork\Infrastructure\DomainEventPublishingRepository;
use SeedWork\Infrastructure\InMemoryIntegrationEventPublisher;
use SeedWork\Infrastructure\RegistryCommandBus;

$repository     = new InMemoryBankAccountRepository();
$integrationPub = new InMemoryIntegrationEventPublisher();

$domainEventBus = new DeferredDomainEventBus();
$domainEventBus->subscribe(AccountOpened::class, new AccountOpenedDomainEventHandler($integrationPub));

$publishingRepo = new DomainEventPublishingRepository($repository, $domainEventBus);

$registry = new RegistryCommandBus();
$registry->register(DepositMoneyCommand::class, new DepositMoneyCommandHandler($publishingRepo));

$commandBus = (new CommandBusBuilder($registry))
    ->withValidation()
    ->withDomainEventCoordination($domainEventBus)
    ->build();

$result = $commandBus->dispatch(new DepositMoneyCommand($accountId, 100, 'USD'));
```

See the full [BankAccount example](examples/BankAccount/) for a complete working implementation including queries, transfers, and a composition root.
