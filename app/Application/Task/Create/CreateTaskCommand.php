<?php

declare(strict_types=1);

namespace App\Application\Task\Create;

/**
 * Command — carries the intent to create a task.
 *
 * Immutable DTO: no logic, no side effects.
 * The Handler decides what to do with it.
 */
final class CreateTaskCommand
{
    public function __construct(
        public readonly int     $userId,
        public readonly string  $title,
        public readonly ?string $description = null,
        public readonly string  $status      = 'pending',
        public readonly string  $priority    = 'medium',
        public readonly ?string $dueDate     = null,
    ) {}
}
