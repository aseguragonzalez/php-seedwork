<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Application\BackgroundTask;

/**
 * In-memory implementation of {@see TaskOutboxRepositorySpy} for use in tests.
 *
 * Generates UUIDs with a simple random implementation to avoid external dependencies.
 *
 * @see TaskOutboxRepositorySpy Test-focused extension implemented here.
 */
final class InMemoryTaskOutboxRepository implements TaskOutboxRepositorySpy
{
    /** @var array<string, TaskOutboxRecord> */
    private array $records = [];

    /**
     * @return array<TaskOutboxRecord>
     */
    public function all(): array
    {
        return array_values($this->records);
    }

    /**
     * Persists a new outbox record with Pending status.
     *
     * @param BackgroundTask $task The task to store.
     */
    public function save(BackgroundTask $task): void
    {
        $record = new TaskOutboxRecord(
            id: $this->generateId(),
            task: $task,
            status: IntegrationEventOutboxStatus::Pending,
            attempts: 0,
            createdAt: new \DateTimeImmutable('now', new \DateTimeZone('UTC'))
        );
        $this->records[$record->id] = $record;
    }

    /**
     * @return array<TaskOutboxRecord>
     */
    public function findPending(int $limit = 100): array
    {
        $pending = array_filter(
            $this->records,
            fn (TaskOutboxRecord $r) => $r->status === IntegrationEventOutboxStatus::Pending
        );
        return array_slice(array_values($pending), 0, $limit);
    }

    public function markAsDelivered(string $id): void
    {
        $record = $this->records[$id] ?? null;
        if ($record === null) {
            return;
        }
        $this->records[$id] = new TaskOutboxRecord(
            id: $record->id,
            task: $record->task,
            status: IntegrationEventOutboxStatus::Published,
            attempts: $record->attempts,
            createdAt: $record->createdAt,
            deliveredAt: new \DateTimeImmutable('now', new \DateTimeZone('UTC'))
        );
    }

    public function markAsFailed(string $id, string $error): void
    {
        $record = $this->records[$id] ?? null;
        if ($record === null) {
            return;
        }
        $this->records[$id] = new TaskOutboxRecord(
            id: $record->id,
            task: $record->task,
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
