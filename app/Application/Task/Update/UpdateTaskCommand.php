<?php

declare(strict_types=1);

namespace App\Application\Task\Update;

final class UpdateTaskCommand
{
    public function __construct(
        public readonly int     $taskId,
        public readonly int     $userId,   // For ownership verification
        public readonly ?string $title       = null,
        public readonly ?string $description = null,
        public readonly ?string $status      = null,
        public readonly ?string $priority    = null,
        public readonly ?string $dueDate     = null,
    ) {}
}
