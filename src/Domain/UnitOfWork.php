<?php

declare(strict_types=1);

namespace SeedWork\Domain;

/**
 * Contract for a unit of work: defines a transaction boundary for persisting
 * domain changes. Implementations are infrastructure-specific (e.g. database
 * transaction, document session).
 *
 * Typical usage: start a session, perform work (e.g. via CommandBus), then
 * either commit (success) or rollback (failure). Consumers provide their own
 * implementation (e.g. Doctrine DBAL, PDO, or an in-memory stub for tests).
 *
 * @see https://martinfowler.com/eaaCatalog/unitOfWork.html Unit of Work (Fowler, PoEAA)
 * @see https://domainlanguage.com/ddd/reference/ Eric Evans, Domain-Driven Design
 */
interface UnitOfWork
{
    /**
     * Starts or opens the unit of work (e.g. begin transaction).
     */
    public function createSession(): void;

    /**
     * Persists changes and closes the session (e.g. commit transaction).
     */
    public function commit(): void;

    /**
     * Discards changes and closes the session (e.g. rollback transaction).
     */
    public function rollback(): void;
}
