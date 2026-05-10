<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Application\IntegrationEvent;
use SeedWork\Application\OutboxRecord;
use SeedWork\Application\OutboxRepository;
use SeedWork\Application\OutboxStatus;

/**
 * In-memory implementation of {@see OutboxRepository} for use in tests and examples.
 *
 * Generates UUIDs with a simple random implementation to avoid external dependencies.
 *
 * @see OutboxRepository Application port this implements.
 */
final class InMemoryOutboxRepository implements OutboxRepository
{
    /** @var array<string, OutboxRecord> */
    private array $records = [];

    /**
     * Persists a new outbox record with Pending status.
     *
     * @param IntegrationEvent $event The event to store.
     */
    public function save(IntegrationEvent $event): void
    {
        $record = new OutboxRecord(
            id: $this->generateId(),
            event: $event,
            status: OutboxStatus::Pending,
            attempts: 0,
            createdAt: new \DateTimeImmutable('now', new \DateTimeZone('UTC'))
        );
        $this->records[$record->id] = $record;
    }

    /**
     * @return array<OutboxRecord>
     */
    public function findPending(int $limit = 100): array
    {
        $pending = array_filter(
            $this->records,
            fn (OutboxRecord $r) => $r->status === OutboxStatus::Pending
        );
        return array_slice(array_values($pending), 0, $limit);
    }

    public function markAsPublished(string $id): void
    {
        $record = $this->records[$id] ?? null;
        if ($record === null) {
            return;
        }
        $this->records[$id] = new OutboxRecord(
            id: $record->id,
            event: $record->event,
            status: OutboxStatus::Published,
            attempts: $record->attempts,
            createdAt: $record->createdAt,
            lastError: $record->lastError,
            publishedAt: new \DateTimeImmutable('now', new \DateTimeZone('UTC'))
        );
    }

    public function markAsFailed(string $id, string $error): void
    {
        $record = $this->records[$id] ?? null;
        if ($record === null) {
            return;
        }
        $this->records[$id] = new OutboxRecord(
            id: $record->id,
            event: $record->event,
            status: OutboxStatus::Failed,
            attempts: $record->attempts + 1,
            createdAt: $record->createdAt,
            lastError: $error
        );
    }

    private function generateId(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}
