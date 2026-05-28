<?php

declare(strict_types=1);

namespace App\Application\Task\Query;

final class ListTasksQuery
{
    public function __construct(
        public readonly int     $userId,
        public readonly int     $perPage  = 15,
        public readonly int     $page     = 1,
        public readonly ?string $status   = null,
        public readonly ?string $priority = null,
        public readonly string  $sort     = 'created_at',
        public readonly string  $order    = 'desc',
    ) {}
}
