# [0.7.0](https://github.com/aseguragonzalez/php-seedwork/compare/v0.6.0...v0.7.0) (2026-05-17)


### Features

* validate Command and Query on instantiation, remove validation bus decorators ([#32](https://github.com/aseguragonzalez/php-seedwork/issues/32)) ([1698c71](https://github.com/aseguragonzalez/php-seedwork/commit/1698c71ef522faed562d6e08f2dcb94733d3ca88))

# [0.6.0](https://github.com/aseguragonzalez/php-seedwork/compare/v0.5.0...v0.6.0) (2026-05-16)


### Features

* rename Result::isFail() to isFailed() for API consistency ([#31](https://github.com/aseguragonzalez/php-seedwork/issues/31)) ([480360e](https://github.com/aseguragonzalez/php-seedwork/commit/480360e852f892aa9e77cd87642c729d5371c431))

# [0.5.0](https://github.com/aseguragonzalez/php-seedwork/compare/v0.4.0...v0.5.0) (2026-05-16)


### Features

* align PHP seedwork API with TypeScript and Python counterparts ([#29](https://github.com/aseguragonzalez/php-seedwork/issues/29)) ([ed10706](https://github.com/aseguragonzalez/php-seedwork/commit/ed10706fca043a2ef59640a589a0aa20fe854d0f))

### Features

# [0.4.0](https://github.com/aseguragonzalez/php-seedwork/compare/v0.3.0...v0.4.0) (2026-05-15)


### Features

* release 0.3.0 ([#26](https://github.com/aseguragonzalez/php-seedwork/issues/26)) ([9df28c7](https://github.com/aseguragonzalez/php-seedwork/commit/9df28c7abc995b900cc8b6d98730d26f9a7fc849))

# [0.3.0](https://github.com/aseguragonzalez/php-seedwork/compare/v0.2.0...v0.3.0) (2026-05-14)


### Features

* release 0.3.0 ([b329a47](https://github.com/aseguragonzalez/php-seedwork/commit/b329a478cec7690c52d6700e0f8f3713f16ee58f))

# [0.2.0](https://github.com/aseguragonzalez/php-seedwork/compare/v0.1.1...v0.2.0) (2026-05-07)


### Features

* align with ts-seedwork — semantic release, new building blocks and repo config ([#23](https://github.com/aseguragonzalez/php-seedwork/issues/23)) ([f774a3f](https://github.com/aseguragonzalez/php-seedwork/commit/f774a3f40b35628aa2f67bf27d48323baa6e59ea))

### Changed

- **Dependencies:** Bumped `friendsofphp/php-cs-fixer` from 3.94.2 to 3.95.1.

## [0.1.1] - 2026-02-27

### Changed

- **Documentation:** Updated CLAUDE instructions for downstream projects.
- **Dependencies:** Updated dependencies to the latest versions.

## [0.1.0] - 2026-02-27

### Changed

- **Documentation:** Updated examples and fixtures for downstream projects.
- **Documentation:** Updated Cursor rules for downstream projects.
- **Documentation:** Updated Copilot instructions for downstream projects.

## [0.0.0-alpha] - 2026-02-22

### Changed

- **Domain layer:** `AggregateRoot`, `Entity`, `ValueObject`, `EntityId`, `EventId`;
  `DomainEvent`; `Repository`, `UnitOfWork`, `AggregateObtainer`; exceptions
  `DomainException`, `ValueException`, `NotFoundResource`.
- **Application layer:** `Command`, `CommandBus`, `CommandHandler`; `Query`,
  `QueryBus`, `QueryHandler`, `QueryResult`; `DomainEventBus`, `DomainEventHandler`.
- **Infrastructure layer:** `ContainerCommandBus`, `ContainerQueryBus`;
  `TransactionalCommandBus`; `DeferredDomainEventBus`, `DomainEventFlushCommandBus`.
- **Documentation:** Component reference, coding standards, best practices;
  BankAccount fixture example (docs/examples/BankAccount/).

[Unreleased]: https://github.com/aseguragonzalez/php-seedwork/compare/v0.1.1...HEAD
[0.1.1]: https://github.com/aseguragonzalez/php-seedwork/compare/v0.1.0...v0.1.1
[0.1.0]: https://github.com/aseguragonzalez/php-seedwork/releases/tag/v0.1.0
[0.0.0-alpha]: https://github.com/aseguragonzalez/php-seedwork/releases/tag/v0.0.0-alpha
