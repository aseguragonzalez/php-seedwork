<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Application\IntegrationEvent;

/**
 * In-memory implementation of {@see IntegrationEventOutboxRepositorySpy} for use in tests.
 *
 * Generates UUIDs with a simple random implementation to avoid external dependencies.
 *
 * @see IntegrationEventOutboxRepositorySpy Test-focused extension implemented here.
 */
final class InMemoryIntegrationEventOutboxRepository implements IntegrationEventOutboxRepositorySpy
{
    /** @var array<string, IntegrationEventOutboxRecord> */
    private array $records = [];

    /**
     * @return array<IntegrationEventOutboxRecord>
     */
    public function all(): array
    {
        return array_values($this->records);
    }

    /**
     * Persists a new outbox record with Pending status.
     *
     * @param IntegrationEvent $event The event to store.
     */
    public function save(IntegrationEvent $event): void
    {
        $record = new IntegrationEventOutboxRecord(
            id: $this->generateId(),
            event: $event,
            status: IntegrationEventOutboxStatus::Pending,
            attempts: 0,
            createdAt: new \DateTimeImmutable('now', new \DateTimeZone('UTC'))
        );
        $this->records[$record->id] = $record;
    }

    /**
     * @return array<IntegrationEventOutboxRecord>
     */
    public function findPending(int $limit = 100): array
    {
        $pending = array_filter(
            $this->records,
            fn (IntegrationEventOutboxRecord $r) => $r->status === IntegrationEventOutboxStatus::Pending
        );
        return array_slice(array_values($pending), 0, $limit);
    }

    public function markAsPublished(string $id): void
    {
        $record = $this->records[$id] ?? null;
        if ($record === null) {
            return;
        }
        $this->records[$id] = new IntegrationEventOutboxRecord(
            id: $record->id,
            event: $record->event,
            status: IntegrationEventOutboxStatus::Published,
            attempts: $record->attempts,
            createdAt: $record->createdAt,
            publishedAt: new \DateTimeImmutable('now', new \DateTimeZone('UTC'))
        );
    }

    public function markAsFailed(string $id, string $error): void
    {
        $record = $this->records[$id] ?? null;
        if ($record === null) {
            return;
        }
        $this->records[$id] = new IntegrationEventOutboxRecord(
            id: $record->id,
            event: $record->event,
            status: IntegrationEventOutboxStatus::Failed,
            attempts: $record->attempts + 1,
            createdAt: $record->createdAt,
            lastError: $error
        );
    }

    public function reset(): void
    {
        $this->records = [];
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
