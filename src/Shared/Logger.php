<?php

declare(strict_types=1);

namespace SeedWork\Shared;

/**
 * Minimal logging port for application and infrastructure layers.
 *
 * Implementations adapt to any logging backend (PSR-3, Monolog, etc.) without
 * introducing a direct coupling to third-party packages. Domain code must never
 * depend on this interface.
 */
interface Logger
{
    /** @param array<string, mixed> $context */
    public function info(string $message, array $context = []): void;

    /** @param array<string, mixed> $context */
    public function warning(string $message, array $context = []): void;

    /** @param array<string, mixed> $context */
    public function error(string $message, array $context = []): void;

    /** @param array<string, mixed> $context */
    public function debug(string $message, array $context = []): void;
}
