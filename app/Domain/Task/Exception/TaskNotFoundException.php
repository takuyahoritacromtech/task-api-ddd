<?php

declare(strict_types=1);

namespace App\Domain\Task\Exception;

use RuntimeException;

final class TaskNotFoundException extends RuntimeException
{
    public static function withId(int $id): self
    {
        return new self("Task #{$id} not found or access denied.");
    }
}
