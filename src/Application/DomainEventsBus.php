<?php

declare(strict_types=1);

namespace Seedwork\Application;

use Seedwork\Domain\DomainEvent;

interface DomainEventsBus
{
    public function publish(DomainEvent $event): void;

    /**
     * @param string $eventType
     * @param DomainEventHandler<DomainEvent> $domainEventHandler
     */
    public function subscribe(string $eventType, DomainEventHandler $domainEventHandler): void;

    public function notify(): void;
}
