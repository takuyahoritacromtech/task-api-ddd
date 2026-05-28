<?php

declare(strict_types=1);

namespace App\Presentation\Http\Resources;

use App\Domain\Task\Entity\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Task
 */
class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Task $task */
        $task = $this->resource;

        return [
            'id'          => $task->id()->value(),
            'title'       => $task->title(),
            'description' => $task->description(),
            'status'      => $task->status()->value(),
            'priority'    => $task->priority()->value(),
            'due_date'    => $task->dueDate()?->format('Y-m-d'),
            'is_overdue'  => $task->isOverdue(),
            'created_at'  => $task->createdAt()->format(\DateTimeInterface::ATOM),
            'updated_at'  => $task->updatedAt()->format(\DateTimeInterface::ATOM),
        ];
    }
}
