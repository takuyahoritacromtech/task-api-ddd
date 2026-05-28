<?php

declare(strict_types=1);

namespace App\Application\Task\Query;

use App\Domain\Task\Repository\TaskRepositoryInterface;

final class ListTasksHandler
{
    public function __construct(
        private readonly TaskRepositoryInterface $repository,
    ) {}

    /**
     * @return array{data: \App\Domain\Task\Entity\Task[], total: int}
     */
    public function handle(ListTasksQuery $query): array
    {
        $filters = array_filter([
            'status'   => $query->status,
            'priority' => $query->priority,
            'sort'     => $query->sort,
            'order'    => $query->order,
        ]);

        return $this->repository->findByUserId(
            $query->userId,
            $query->perPage,
            $query->page,
            $filters
        );
    }
}
