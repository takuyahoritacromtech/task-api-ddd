<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Task\Entity\Task;
use App\Domain\Task\Exception\TaskNotFoundException;
use App\Domain\Task\ValueObject\TaskId;
use App\Domain\Task\ValueObject\TaskPriority;
use App\Domain\Task\ValueObject\TaskStatus;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Pure unit tests — NO database, NO framework.
 *
 * These tests run in milliseconds and prove domain rules work correctly.
 */
class TaskTest extends TestCase
{
    // ---------- TaskStatus ----------

    public function test_status_rejects_invalid_value(): void
    {
        $this->expectException(InvalidArgumentException::class);
        TaskStatus::fromString('done');
    }

    public function test_completed_task_cannot_revert_to_pending(): void
    {
        $status = TaskStatus::completed();
        $this->expectException(InvalidArgumentException::class);
        $status->transitionTo(TaskStatus::pending());
    }

    public function test_in_progress_can_transition_to_completed(): void
    {
        $status = TaskStatus::inProgress();
        $next   = $status->transitionTo(TaskStatus::completed());
        $this->assertTrue($next->isCompleted());
    }

    // ---------- TaskPriority ----------

    public function test_priority_rejects_invalid_value(): void
    {
        $this->expectException(InvalidArgumentException::class);
        TaskPriority::fromString('urgent');
    }

    public function test_priority_ordering(): void
    {
        $high   = TaskPriority::fromString('high');
        $medium = TaskPriority::fromString('medium');
        $this->assertTrue($high->isHigherThan($medium));
        $this->assertFalse($medium->isHigherThan($high));
    }

    // ---------- TaskId ----------

    public function test_task_id_rejects_zero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new TaskId(0);
    }

    public function test_task_id_rejects_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new TaskId(-1);
    }

    // ---------- Task Entity ----------

    public function test_task_create_sets_defaults(): void
    {
        $task = Task::create(userId: 1, title: 'Write tests');

        $this->assertSame('Write tests', $task->title());
        $this->assertSame('pending',     $task->status()->value());
        $this->assertSame('medium',      $task->priority()->value());
        $this->assertNull($task->dueDate());
        $this->assertFalse($task->isOverdue());
    }

    public function test_task_title_cannot_be_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Task::create(userId: 1, title: '   ');
    }

    public function test_task_title_cannot_exceed_200_chars(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Task::create(userId: 1, title: str_repeat('a', 201));
    }

    public function test_task_ownership_check(): void
    {
        $task = Task::create(userId: 42, title: 'My task');
        $this->assertTrue($task->isOwnedBy(42));
        $this->assertFalse($task->isOwnedBy(99));
    }

    public function test_task_complete_updates_status(): void
    {
        $task = Task::create(userId: 1, title: 'Finish me');
        $task->complete();
        $this->assertTrue($task->status()->isCompleted());
    }

    public function test_task_overdue_detection(): void
    {
        $task = Task::create(
            userId:  1,
            title:   'Overdue task',
            dueDate: '2000-01-01',  // Past date
        );
        $this->assertTrue($task->isOverdue());

        $task->complete();
        $this->assertFalse($task->isOverdue()); // Completed tasks are not overdue
    }

    public function test_task_not_found_exception_message(): void
    {
        $e = TaskNotFoundException::withId(99);
        $this->assertStringContainsString('99', $e->getMessage());
    }
}
