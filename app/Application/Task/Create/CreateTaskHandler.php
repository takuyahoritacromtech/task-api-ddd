<?php

declare(strict_types=1);

namespace App\Application\Task\Create;

use App\Domain\Task\Entity\Task;
use App\Domain\Task\Repository\TaskRepositoryInterface;

/**
 * Handler — orchestrates the Create Task use-case.
 *
 * Depends on the domain interface, not on Eloquent.
 * Can be unit-tested without hitting the database.
 */
final class CreateTaskHandler
{
    public function __construct(
        private readonly TaskRepositoryInterface $repository,
    ) {}

    public function handle(CreateTaskCommand $command): Task
    {
        $task = Task::create(
            userId:      $command->userId,
            title:       $command->title,
            description: $command->description,
            status:      $command->status,
            priority:    $command->priority,
            dueDate:     $command->dueDate,
        );

        return $this->repository->save($task);
    }
}
