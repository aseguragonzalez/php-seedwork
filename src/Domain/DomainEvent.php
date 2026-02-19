<?php

declare(strict_types=1);

namespace Seedwork\Domain;

abstract readonly class DomainEvent
{
    /**
     * @param array<mixed> $payload
     */
    protected function __construct(
        public EventId $id,
        public string $type,
        public string $version,
        public array $payload = [],
        public \DateTimeImmutable $createdAt = new \DateTimeImmutable(
            'now',
            new \DateTimeZone('UTC')
        )
    ) {
    }

    public function equals(DomainEvent $other): bool
    {
        return $this->id->equals($other->id);
    }
}
