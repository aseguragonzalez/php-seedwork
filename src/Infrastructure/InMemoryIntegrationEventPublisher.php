<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Application\IntegrationEvent;
use SeedWork\Application\IntegrationEventPublisher;

/**
 * In-memory implementation of {@see IntegrationEventPublisher} for use in tests.
 *
 * @see IntegrationEventPublisher Application port this implements.
 */
final class InMemoryIntegrationEventPublisher implements IntegrationEventPublisher
{
    /** @var list<IntegrationEvent> */
    private array $published = [];

    /**
     * @param array<IntegrationEvent> $events
     */
    public function publish(array $events): void
    {
        foreach ($events as $event) {
            $this->published[] = $event;
        }
    }

    /**
     * @return list<IntegrationEvent>
     */
    public function getPublished(): array
    {
        return $this->published;
    }

    public function clear(): void
    {
        $this->published = [];
    }
}
