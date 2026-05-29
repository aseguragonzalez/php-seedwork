<?php

declare(strict_types=1);

namespace Tests\Domain;

use PHPUnit\Framework\TestCase;
use SeedWork\Domain\Entity;
use Tests\Fixtures\TestEntity;
use Tests\Fixtures\TestId;

/**
 * @internal
 *
 * @coversNothing
 */
final class EntityTest extends TestCase
{
    public function testEquals(): void
    {
        $entity = TestEntity::create();
        $entity2 = TestEntity::build($entity->id, $entity->createdAt);
        $entity3 = TestEntity::create();

        $this->assertTrue($entity->equals($entity2));
        $this->assertFalse($entity->equals($entity3));
    }

    public function testEqualsReturnsFalseForDifferentConcreteType(): void
    {
        $id = TestId::create();
        $entity = TestEntity::create($id);

        $otherType = new readonly class($id) extends Entity {
            public function __construct(TestId $id)
            {
                parent::__construct($id);
            }

            protected function validate(): void {}
        };

        $this->assertFalse($entity->equals($otherType));
    }

    public function testEqualsReturnsFalseForScalarIdsThatLooseEqualityWouldConfuse(): void
    {
        $quirkyId = new readonly class('0e5') extends Entity {
            public function __construct(string $id)
            {
                parent::__construct($id);
            }

            protected function validate(): void {}
        };

        $zeroId = new readonly class(0) extends Entity {
            public function __construct(int $id)
            {
                parent::__construct($id);
            }

            protected function validate(): void {}
        };

        // "0e5" == 0 is true under PHP loose equality; (string)"0e5" === (string)0 is false
        $this->assertFalse($quirkyId->equals($zeroId));
    }

    public function testValidate(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('TestEntity createdAt cannot be in the future.');

        TestEntity::create(createdAt: new \DateTimeImmutable('tomorrow'));
    }
}
