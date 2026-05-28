<?php

declare(strict_types=1);

namespace App\Application\Task\Delete;

final class DeleteTaskCommand
{
    public function __construct(
        public readonly int $taskId,
        public readonly int $userId,
    ) {}
}
