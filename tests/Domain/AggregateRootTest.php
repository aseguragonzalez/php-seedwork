<?php

declare(strict_types=1);

namespace Tests\Domain;

use PHPUnit\Framework\TestCase;
use SeedWork\Domain\AggregateRoot;
use Tests\Fixtures\AnotherTestEvent;
use Tests\Fixtures\TestAggregate;
use Tests\Fixtures\TestEvent;
use Tests\Fixtures\TestId;

final class AggregateRootTest extends TestCase
{
    public function testEquals(): void
    {
        $aggregate1 = TestAggregate::create();
        $aggregate2 = TestAggregate::create();
        $aggregate3 = TestAggregate::build($aggregate1->id);

        $this->assertFalse($aggregate1->equals($aggregate2));
        $this->assertTrue($aggregate1->equals($aggregate3));
    }

    public function testEqualsReturnsFalseForDifferentConcreteType(): void
    {
        $id = TestId::create();
        $aggregate = TestAggregate::build($id);

        $otherType = new readonly class ($id) extends AggregateRoot {
            public function __construct(TestId $id)
            {
                parent::__construct($id);
            }

            protected function validate(): void
            {
            }
        };

        $this->assertFalse($aggregate->equals($otherType));
    }

    public function testGetDomainEvents(): void
    {
        $event1 = TestEvent::create('first');
        $event2 = AnotherTestEvent::create();

        $aggregate = TestAggregate::create()
            ->withEvent($event1)
            ->withEvent($event2);

        $events = $aggregate->getDomainEvents();

        $this->assertCount(2, $events);
        $this->assertInstanceOf(TestEvent::class, $events[0]);
        $this->assertInstanceOf(AnotherTestEvent::class, $events[1]);
    }
}
