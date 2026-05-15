<?php

declare(strict_types=1);

namespace Tests\Domain;

use PHPUnit\Framework\TestCase;
use Tests\Fixtures\TestEvent;
use Tests\Fixtures\TestEventId;

final class DomainEventTest extends TestCase
{
    public function testEquals(): void
    {
        $event1 = TestEvent::create('payment.processed');
        $event2 = TestEvent::create('payment.processed');
        /** @var TestEventId $id */
        $id = $event1->id;
        $event3 = TestEvent::create('payment.processed', $id);

        $this->assertFalse($event1->equals($event2));
        $this->assertTrue($event1->equals($event3));
    }
}
