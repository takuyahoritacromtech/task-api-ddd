<?php

declare(strict_types=1);

namespace App\Domain\Task\Repository;

use App\Domain\Task\Entity\Task;
use App\Domain\Task\ValueObject\TaskId;

/**
 * Repository Interface — defined in the Domain layer.
 *
 * The Domain knows WHAT it needs; it does NOT know HOW data is stored.
 * The concrete implementation lives in Infrastructure (Eloquent, etc.).
 */
interface TaskRepositoryInterface
{
    /**
     * Persist a new Task and return it with its assigned ID.
     */
    public function save(Task $task): Task;

    /**
     * Persist updates to an existing Task.
     */
    public function update(Task $task): void;

    /**
     * Find a Task by its identity.
     */
    public function findById(TaskId $id): ?Task;

    /**
     * Find a Task that belongs to a specific user.
     */
    public function findByIdAndUserId(TaskId $id, int $userId): ?Task;

    /**
     * Return paginated tasks for a user with optional filters.
     *
     * @param array{status?: string, priority?: string, sort?: string, order?: string} $filters
     * @return array{data: Task[], total: int}
     */
    public function findByUserId(int $userId, int $perPage, int $page, array $filters): array;

    /**
     * Soft-delete a Task.
     */
    public function delete(Task $task): void;

    /**
     * Bulk-update status for a set of Task IDs owned by a user.
     *
     * @param int[] $ids
     */
    public function bulkUpdateStatus(array $ids, int $userId, string $status): int;

    /**
     * Bulk-delete Task IDs owned by a user.
     *
     * @param int[] $ids
     */
    public function bulkDelete(array $ids, int $userId): int;
}
