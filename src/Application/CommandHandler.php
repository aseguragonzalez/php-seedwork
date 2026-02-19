<?php

declare(strict_types=1);

namespace Seedwork\Application;

/**
 * @template T of Command
 */
interface CommandHandler
{
    /**
     * @param T $command
     */
    public function handle(Command $command): void;
}
