<?php

declare(strict_types=1);

namespace App\Application\Task\Update;

use App\Domain\Task\Entity\Task;
use App\Domain\Task\Exception\TaskNotFoundException;
use App\Domain\Task\Repository\TaskRepositoryInterface;
use App\Domain\Task\ValueObject\TaskId;

final class UpdateTaskHandler
{
    public function __construct(
        private readonly TaskRepositoryInterface $repository,
    ) {}

    public function handle(UpdateTaskCommand $command): Task
    {
        $task = $this->repository->findByIdAndUserId(
            new TaskId($command->taskId),
            $command->userId
        );

        if ($task === null) {
            throw TaskNotFoundException::withId($command->taskId);
        }

        $task->update(
            title:       $command->title,
            description: $command->description,
            status:      $command->status,
            priority:    $command->priority,
            dueDate:     $command->dueDate,
        );

        $this->repository->update($task);

        return $task;
    }
}
