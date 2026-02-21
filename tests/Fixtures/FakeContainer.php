<?php

namespace Tests\Fixtures;

use Psr\Container\ContainerInterface;

final class FakeContainer implements ContainerInterface
{
    /** @var array<string, object> */
    private array $entries;

    /** @param array<string, object> $entries */
    public function __construct(array $entries = [])
    {
        $this->entries = $entries;
    }

    public function get(string $id): mixed
    {
        if (!isset($this->entries[$id])) {
            throw new \RuntimeException(sprintf('Not found: %s', $id));
        }

        return $this->entries[$id];
    }

    public function has(string $id): bool
    {
        return isset($this->entries[$id]);
    }
}
