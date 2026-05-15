<?php

declare(strict_types=1);

namespace Tests\Domain;

use PHPUnit\Framework\TestCase;
use Tests\Fixtures\AnotherTestEvent;
use Tests\Fixtures\TestAggregate;
use Tests\Fixtures\TestEvent;

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

    public function testCollectEvents(): void
    {
        $event1 = TestEvent::create('first');
        $event2 = AnotherTestEvent::create();

        $aggregate = TestAggregate::create()
            ->withEvent($event1)
            ->withEvent($event2);

        $events = $aggregate->collectEvents();

        $this->assertCount(2, $events);
        $this->assertInstanceOf(TestEvent::class, $events[0]);
        $this->assertInstanceOf(AnotherTestEvent::class, $events[1]);
    }
}
