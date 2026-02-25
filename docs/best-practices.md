# Best Practices

This guide explains how to use the SeedWork package effectively in your project.

## Aggregate design

- **Keep aggregates small.** Prefer a small object graph and a clear consistency
  boundary. Avoid large aggregates that are hard to reason about and serialize.
- **Reference other aggregates by identity only.** Do not hold references to
  other aggregate roots; use their `EntityId` instead. Cross-aggregate
  operations are coordinated in the application layer (e.g. load both, call
  behavior on each, save both).
- **Ensure isolation between bounded contexts.** Avoid cross-context references between aggregates.
  Use domain events (reactive integration) and Anti-corruption Layer (ACL) patterns
  (for querying data from other contexts) to isolate bounded contexts.
- **Enforce invariants in the root.** All rules that must always hold (e.g.
  balance ≥ 0, required fields) should be enforced in the aggregate root (or
  entities/value objects inside it). Reject invalid state in constructors and
  behavior methods by throwing domain exceptions.
- **Return new instances from behavior methods.** Do not mutate the aggregate
  and then emit an event. Instead, compute the new state, create a new aggregate
  instance, append the domain event(s), and return it. The handler saves the
  returned instance and publishes `collectEvents()`.

## Commands and handlers

- **Commands carry intent and data only.** The handler is responsible for
  loading the aggregate (e.g. via `AggregateObtainer`), calling the
  appropriate domain method(s), persisting, etc.
  Keep handlers thin: orchestration only, no business logic.
- **Use AggregateObtainer in handlers.** It gives you "load by id or throw"
  with a consistent `NotFoundResource` message. Avoid repeating `findBy` + null
  check and custom exception handling in every handler.
- **Stack buses in the right order.** Use:
  `TransactionalCommandBus(DomainEventFlushCommandBus(ContainerCommandBus),
  UnitOfWork)`. The transaction wraps both the command and the event flush, so
  events are only dispatched after a successful commit. Exceptions trigger
  rollback and no flush.

## Domain events

- **Record events when something meaningful happens.** In the aggregate, append
  a domain event when you create a new instance after a state change (e.g.
  withdrawal, deposit). Use a static factory on the event (e.g.
  `MoneyDeposited::create(...)`) and pass through existing events when building
  the new aggregate instance.
- **Use a deferred event bus and flush after the command.** Buffer events during
  command handling and call `flush()` only after the command succeeds.
  This avoids publishing events for rolled-back work and keeps
  ordering predictable.
- **When to use the deferred event bus.** The deferred event bus is well-suited
  for **monolithic** systems where you want: **isolation between bounded
  contexts** (events are processed after the transaction, within the same
  process); **transactionality** (events are only flushed after a successful
  commit—on rollback, nothing is published); and **no dependency on message
  brokers** (no RabbitMQ, Kafka, etc.; everything runs in-process after the
  transaction). Prefer it for **API or MVC applications** where the **incoming
  request is the transaction boundary** and you do **not** need to synchronize
  with multiple external systems (e.g. a single database for the write, no
  outbound messaging in the same request). For cross-service or async
  integration, a message broker and a different bus implementation are more
  appropriate. Keep using the stacking order: Transactional → Flush → Container.
- **Design event handlers for a single concern.** One handler per event type and
  concern (e.g. update read model, send notification). If the bus can redeliver
  (e.g. async), make handlers idempotent (e.g. by event id) where possible.

## Queries and read model

- **Return DTOs, not domain entities.** Query handlers should return
  `QueryResult` subclasses (or similar DTOs) with primitive or simple fields.
  Map from domain or from a read model (e.g. projection) to these DTOs. This
  keeps the read side stable and serializable.
- **Use QueryRepository for the read side.** Use `getById(string $id)` for a
  single projection and `filter(int $offset, int $limit, array $filters)` for a
  slice. Implement the port in infrastructure (e.g. DB or in-memory for tests).
- **For `filter()`, use FilterCriteria subclasses.** Extend
  `SeedWork\Application\FilterCriteria` and implement `validate()` for allowed
  fields and value shape (e.g. BETWEEN requires an array of two elements). Pass
  an array of criteria to `filter()`; implementations interpret `FilterOperator`
  (EQ, NEQ, GT, GTE, LT, LTE, IN, BETWEEN, LIKE).
- **Keep projections as simple DTOs.** Use plain DTOs (e.g. `BankAccountProjection`)
  for the read model and map them to `QueryResult` in the handler so the port
  boundary stays stable.
- **Keep query handlers read-only.** Do not dispatch commands or change state
  inside a query handler. Queries are for reading; use commands for writes.

## Entry points (controllers, API)

- **Keep controllers thin.** Map the incoming request to a Command or Query,
  dispatch via `CommandBus` or `QueryBus`, then map the `QueryResult` or
  command outcome to the HTTP/response. Do not put domain logic or
  infrastructure (repositories, event bus, etc.) in the controller; depend
  only on the bus interfaces.
- **See the [component reference](component-reference.md#using-the-application-ports-from-an-entry-point)**
  for a concrete controller example using CommandBus and QueryBus.

## Dependency direction

- **Domain does not depend on Application or Infrastructure.** The domain
  layer only depends on SeedWork domain types (and PHP built-ins). No
  framework, no database, no HTTP.
- **Application depends on Domain and application ports.** Use cases (handlers)
  depend on repository interfaces, event bus interface, domain ports (services), and domain types. They
  do not depend on concrete infrastructure.
- **Infrastructure implements interfaces and depends inward.** Implement
  `Repository`, `UnitOfWork`, etc in infrastructure. Depend on domain and
  application interfaces, not the other way around.

## Testing

- **Domain: unit test entities, value objects, and aggregate behavior.** Test
  invariants, validation, equality, and that behavior methods return the
  expected new state and events. Use mocks or stubs for repositories when the
  domain is used in a larger flow.
- **Handlers: unit test with fakes.** Use mocks and stubs for dependencies and a test
  double for the event bus to verify that the handler loads the right
  aggregate, calls the right domain method, saves, and publishes the expected
  events. Add integration tests with a real bus and persistence when you need to
  validate the full stack.

## Summary

| Area | Practice |
| --- | --- |
| Aggregates | Small, reference others by id, enforce invariants, return new instance + events |
| Commands | Thin handlers; obtain → domain → save → publish events; stack Transaction → Flush → Container |
| Events | Record on meaningful change; deferred flush after command; one concern per handler; idempotent when async |
| Queries | Return DTOs; read-only handlers |
| Dependencies | Domain pure; application uses ports; infrastructure implements and points inward |
| Testing | Unit test domain and handlers with fakes; integration test when needed |
