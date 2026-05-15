<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use SeedWork\Domain\AggregateRoot;
use SeedWork\Domain\DomainEvent;

/**
 * @extends AggregateRoot<TestId>
 */
final readonly class TestAggregate extends AggregateRoot
{
    /**
     * @param array<DomainEvent> $domainEvents
     */
    private function __construct(
        TestId $id,
        array $domainEvents = [],
    ) {
        parent::__construct($id, $domainEvents);
    }

    protected function validate(): void
    {
    }

    public static function create(?TestId $id = null): self
    {
        return new self($id ?? TestId::create());
    }

    public static function build(TestId $id): self
    {
        return new self($id, domainEvents: []);
    }

    public function withEvent(DomainEvent $event): self
    {
        return new self($this->id, [...$this->collectEvents(), $event]);
    }
}
