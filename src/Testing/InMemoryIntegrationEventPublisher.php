<?php

declare(strict_types=1);

namespace SeedWork\Testing;

use SeedWork\Application\IntegrationEvent;

/**
 * In-memory implementation of {@see IntegrationEventPublisherSpy} for use in tests.
 *
 * @see IntegrationEventPublisherSpy Spy interface implemented here.
 */
final class InMemoryIntegrationEventPublisher implements IntegrationEventPublisherSpy
{
    /** @var list<IntegrationEvent> */
    private array $published = [];

    public function publish(IntegrationEvent $event): void
    {
        $this->published[] = $event;
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
