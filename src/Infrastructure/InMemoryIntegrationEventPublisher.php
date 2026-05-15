<?php

declare(strict_types=1);

namespace SeedWork\Infrastructure;

use SeedWork\Application\IntegrationEvent;
use SeedWork\Infrastructure\IntegrationEventPublisherSpy;

/**
 * In-memory implementation of {@see IntegrationEventPublisherSpy} for use in tests.
 *
 * @see IntegrationEventPublisherSpy Test-focused extension implemented here.
 */
final class InMemoryIntegrationEventPublisher implements IntegrationEventPublisherSpy
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
    public function published(): array
    {
        return $this->published;
    }

    public function reset(): void
    {
        $this->published = [];
    }
}
