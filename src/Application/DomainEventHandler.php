<?php

declare(strict_types=1);

namespace Seedwork\Application;

use Seedwork\Domain\DomainEvent;

/**
 * @template TEvent of DomainEvent
 */
interface DomainEventHandler
{
    /**
     * @param TEvent $event
     */
    public function execute(DomainEvent $event): void;
}
