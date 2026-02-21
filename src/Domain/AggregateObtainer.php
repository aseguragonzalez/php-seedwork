<?php

declare(strict_types=1);

namespace Seedwork\Domain;

use Seedwork\Domain\Exceptions\NotFoundResource;

/**
 * Base component to obtain an aggregate by id or throw when not found.
 *
 * Extend this class per aggregate type, inject the corresponding repository
 * and resource name via the parent constructor so {@see NotFoundResource} messages are descriptive.
 * Handlers can depend on the concrete obtainer instead of repeating findBy + null check.
 *
 * @template T of AggregateRoot
 */
abstract readonly class AggregateObtainer
{
    /**
     * @param Repository<T> $repository Repository for the aggregate type.
     * @param string $resourceName Name used in {@see NotFoundResource} messages (e.g. "BankAccount").
     */
    public function __construct(
        private Repository $repository,
        private string $resourceName
    ) {
    }

    /**
     * Returns the aggregate for the given id.
     *
     * @throws NotFoundResource When no aggregate exists for the id.
     * @return T The aggregate found.
     */
    public function obtain(EntityId $id): AggregateRoot
    {
        $aggregate = $this->repository->findBy($id);
        if ($aggregate === null) {
            throw new NotFoundResource($this->resourceName, $id);
        }

        return $aggregate;
    }
}
