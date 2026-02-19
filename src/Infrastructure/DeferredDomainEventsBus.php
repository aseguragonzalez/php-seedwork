<?php

declare(strict_types=1);

namespace Seedwork\Infrastructure;

use Seedwork\Application\DomainEventHandler;
use Seedwork\Application\DomainEventsBus;
use Seedwork\Domain\DomainEvent;

final class DeferredDomainEventsBus implements DomainEventsBus
{
    /**
     * @var array<DomainEvent>
     */
    private array $buffer = [];

    /**
     * @var array<string, array<DomainEventHandler<DomainEvent>>>
     */
    private array $handlers = [];

    public function publish(DomainEvent $event): void
    {
        $this->buffer[] = $event;
    }

    /**
     * @param string $eventType
     * @param DomainEventHandler<DomainEvent> $domainEventHandler
     */
    public function subscribe(string $eventType, DomainEventHandler $domainEventHandler): void
    {
        if (!isset($this->handlers[$eventType])) {
            $this->handlers[$eventType] = [];
        }
        $this->handlers[$eventType][] = $domainEventHandler;
    }

    public function notify(): void
    {
        $events = $this->buffer;
        $this->buffer = [];

        foreach ($events as $event) {
            $eventType = $event::class;
            if (!isset($this->handlers[$eventType])) {
                continue;
            }

            foreach ($this->handlers[$eventType] as $handler) {
                $handler->execute($event);
            }
        }
    }
}
