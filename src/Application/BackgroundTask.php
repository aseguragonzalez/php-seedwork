<?php

declare(strict_types=1);

namespace SeedWork\Application;

/**
 * Base type for background tasks — units of work scheduled for async execution.
 *
 * Carries only the static payload needed to execute the task. Lifecycle state
 * (status, attempts, timestamps) belongs to the task manager / outbox, not here.
 *
 * Subclasses add domain-specific fields. All fields are primitives so the task
 * is trivially serializable.
 *
 * @see TaskScheduler Port for scheduling tasks.
 * @see TaskHandler   Handler that executes a specific task type.
 */
abstract readonly class BackgroundTask
{
    /**
     * @param string                     $id            unique task ID (UUID)
     * @param string                     $type          Task type identifier (e.g. 'domain.action_name').
     * @param array<string, mixed>       $payload       serializable primitive arguments
     * @param string                     $correlationId correlation ID for distributed tracing (required)
     * @param null|string                $causationId   ID of the command or event that caused this task
     * @param null|array<string, string> $metadata      optional trace/tenant metadata
     */
    public function __construct(
        public string $id,
        public string $type,
        public array $payload,
        public string $correlationId,
        public ?string $causationId = null,
        public ?array $metadata = null
    ) {}
}
