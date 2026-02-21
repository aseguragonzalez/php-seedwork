<?php

declare(strict_types=1);

namespace Seedwork\Infrastructure;

use Seedwork\Application\DomainEventBus;
use Seedwork\Application\DomainEventHandler;
use Seedwork\Domain\DomainEvent;

final class DeferredDomainEventBus implements DomainEventBus
{
    /**
     * @var array<DomainEvent>
     */
    private array $buffer = [];

    /**
     * @var array<string, array<DomainEventHandler<DomainEvent>>>
     */
    private array $handlers = [];

    /**
     * @param array<DomainEvent> $events
     */
    public function publish(array $events): void
    {
        $this->buffer = array_merge($this->buffer, $events);
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

    public function flush(): void
    {
        $events = $this->buffer;
        $this->buffer = [];

        foreach ($events as $event) {
            $eventType = $event::class;
            if (!isset($this->handlers[$eventType])) {
                continue;
            }

            foreach ($this->handlers[$eventType] as $handler) {
                $handler->handle($event);
            }
        }
    }
}
