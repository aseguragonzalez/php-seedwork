<?php

declare(strict_types=1);

namespace Seedwork\Domain\Exceptions;

use Seedwork\Domain\EntityId;

final class NotFoundResource extends DomainException
{
    public function __construct(string $resourceName, ?EntityId $id = null, int $code = 0)
    {
        $message = $id !== null
            ? sprintf("Resource '%s' not found for id '%s'", $resourceName, $id->value)
            : sprintf("Resource '%s' not found", $resourceName);
        parent::__construct($message, $code);
    }
}
