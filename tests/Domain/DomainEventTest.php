<?php

declare(strict_types=1);

namespace Tests\Domain;

use PHPUnit\Framework\TestCase;
use Tests\Fixtures\TestEvent;

final class DomainEventTest extends TestCase
{
    public function testEquals(): void
    {
        $event1 = TestEvent::create('payment.processed');
        $event2 = TestEvent::create('payment.processed');
        $id = $event1->id;
        $event3 = TestEvent::create('payment.processed', $id);

        $this->assertFalse($event1->equals($event2));
        $this->assertTrue($event1->equals($event3));
    }

    public function testAggregateIdIsStored(): void
    {
        $event = TestEvent::create('payment.processed', aggregateId: 'account-42');

        $this->assertSame('account-42', $event->aggregateId);
    }

    public function testEmptyIdIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        TestEvent::create(id: '');
    }

    public function testEmptyAggregateIdIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        TestEvent::create(aggregateId: '');
    }
}
