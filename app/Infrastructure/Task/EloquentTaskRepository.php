<?php

declare(strict_types=1);

namespace App\Infrastructure\Task;

use App\Domain\Task\Entity\Task;
use App\Domain\Task\Repository\TaskRepositoryInterface;
use App\Domain\Task\ValueObject\TaskId;

/**
 * Concrete Repository — Eloquent implementation.
 *
 * Translates between Domain Entities and Eloquent models.
 * The Domain never touches this class directly.
 */
final class EloquentTaskRepository implements TaskRepositoryInterface
{
    public function save(Task $task): Task
    {
        $model = EloquentTaskModel::create([
            'user_id'     => $task->userId(),
            'title'       => $task->title(),
            'description' => $task->description(),
            'status'      => $task->status()->value(),
            'priority'    => $task->priority()->value(),
            'due_date'    => $task->dueDate()?->format('Y-m-d'),
        ]);

        return $this->toDomain($model);
    }

    public function update(Task $task): void
    {
        EloquentTaskModel::where('id', $task->id()->value())->update([
            'title'       => $task->title(),
            'description' => $task->description(),
            'status'      => $task->status()->value(),
            'priority'    => $task->priority()->value(),
            'due_date'    => $task->dueDate()?->format('Y-m-d'),
            'updated_at'  => $task->updatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    public function findById(TaskId $id): ?Task
    {
        $model = EloquentTaskModel::find($id->value());
        return $model ? $this->toDomain($model) : null;
    }

    public function findByIdAndUserId(TaskId $id, int $userId): ?Task
    {
        $model = EloquentTaskModel::where('id', $id->value())
            ->where('user_id', $userId)
            ->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function findByUserId(int $userId, int $perPage, int $page, array $filters): array
    {
        $allowed = ['created_at', 'due_date', 'priority'];
        $sort    = in_array($filters['sort'] ?? '', $allowed, true) ? $filters['sort'] : 'created_at';
        $order   = ($filters['order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $query = EloquentTaskModel::where('user_id', $userId)
            ->when($filters['status']   ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['priority'] ?? null, fn ($q, $v) => $q->where('priority', $v))
            ->orderBy($sort, $order);

        $total  = $query->count();
        $models = $query->forPage($page, $perPage)->get();

        return [
            'data'  => $models->map(fn ($m) => $this->toDomain($m))->all(),
            'total' => $total,
        ];
    }

    public function delete(Task $task): void
    {
        EloquentTaskModel::find($task->id()->value())?->delete();
    }

    public function bulkUpdateStatus(array $ids, int $userId, string $status): int
    {
        return EloquentTaskModel::whereIn('id', $ids)
            ->where('user_id', $userId)
            ->update(['status' => $status]);
    }

    public function bulkDelete(array $ids, int $userId): int
    {
        return EloquentTaskModel::whereIn('id', $ids)
            ->where('user_id', $userId)
            ->delete();
    }

    // ---------- Mapping ----------

    private function toDomain(EloquentTaskModel $model): Task
    {
        return Task::reconstruct(
            id:          $model->id,
            userId:      $model->user_id,
            title:       $model->title,
            description: $model->description,
            status:      $model->status,
            priority:    $model->priority,
            dueDate:     $model->due_date?->toDateString(),
            createdAt:   $model->created_at->toDateTimeString(),
            updatedAt:   $model->updated_at->toDateTimeString(),
        );
    }
}
