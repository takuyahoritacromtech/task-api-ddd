<?php

declare(strict_types=1);

namespace App\Application\Task\Delete;

use App\Domain\Task\Exception\TaskNotFoundException;
use App\Domain\Task\Repository\TaskRepositoryInterface;
use App\Domain\Task\ValueObject\TaskId;

final class DeleteTaskHandler
{
    public function __construct(
        private readonly TaskRepositoryInterface $repository,
    ) {}

    public function handle(DeleteTaskCommand $command): void
    {
        $task = $this->repository->findByIdAndUserId(
            new TaskId($command->taskId),
            $command->userId
        );

        if ($task === null) {
            throw TaskNotFoundException::withId($command->taskId);
        }

        $this->repository->delete($task);
    }
}
