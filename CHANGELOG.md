# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.1.0] - 2026-02-27

### Changed

- **Documentation:** Updated examples and fixtures for downstream projects.
- **Documentation:** Updated Cursor rules for downstream projects.
- **Documentation:** Updated Copilot instructions for downstream projects.

## [0.0.0-alpha] - 2026-02-22

### Added

- **Domain layer:** `AggregateRoot`, `Entity`, `ValueObject`, `EntityId`, `EventId`;
  `DomainEvent`; `Repository`, `UnitOfWork`, `AggregateObtainer`; exceptions
  `DomainException`, `ValueException`, `NotFoundResource`.
- **Application layer:** `Command`, `CommandBus`, `CommandHandler`; `Query`,
  `QueryBus`, `QueryHandler`, `QueryResult`; `DomainEventBus`, `DomainEventHandler`.
- **Infrastructure layer:** `ContainerCommandBus`, `ContainerQueryBus`;
  `TransactionalCommandBus`; `DeferredDomainEventBus`, `DomainEventFlushCommandBus`.
- **Documentation:** Component reference, coding standards, best practices;
  BankAccount fixture example (tests/Fixtures/BankAccount/).

[Unreleased]: https://github.com/aseguragonzalez/php-seedwork/compare/php-seedwork-v0.0.0-alpha...HEAD
[0.0.0-alpha]: https://github.com/aseguragonzalez/php-seedwork/releases/tag/php-seedwork-v0.0.0-alpha
