<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Application\IntegrationEvent;

/**
 * In-memory implementation of {@see IntegrationEventOutboxRepositorySpy} for use in tests.
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

    public function save(IntegrationEvent $event): void
    {
        if (array_key_exists($event->id, $this->records)) {
            return;
        }
        $record = new IntegrationEventOutboxRecord(
            id: $event->id,
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
}
