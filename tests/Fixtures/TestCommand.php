<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use SeedWork\Application\Command;
use SeedWork\Application\ValidationErrorDetail;
use SeedWork\Application\ValidationErrors;

final readonly class TestCommand extends Command
{
    public function __construct(public string $payload = 'test')
    {
        parent::__construct();
    }

    public function validate(): void
    {
        if (empty($this->payload)) {
            throw new ValidationErrors([
                new ValidationErrorDetail('payload_required', 'Payload is required.'),
            ]);
        }
    }
}
