<?php

declare(strict_types=1);

namespace Tests\Domain;

use PHPUnit\Framework\TestCase;
use SeedWork\Domain\Exceptions\DomainException;
use Tests\Fixtures\TestEntity;

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

    public function testValidate(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('TestEntity createdAt cannot be in the future.');

        TestEntity::create(createdAt: new \DateTimeImmutable('tomorrow'));
    }
}
