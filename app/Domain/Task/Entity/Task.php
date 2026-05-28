<?php

declare(strict_types=1);

namespace App\Domain\Task\Entity;

use App\Domain\Task\ValueObject\TaskId;
use App\Domain\Task\ValueObject\TaskPriority;
use App\Domain\Task\ValueObject\TaskStatus;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Domain Entity — Task.
 *
 * Pure PHP class with NO framework dependency.
 * Business rules live here, not in controllers or services.
 *
 * Note on identity:
 *   A newly created task has no ID (null) until persisted by the repository.
 *   Use Task::reconstruct() when re-hydrating from storage — that always has an ID.
 */
final class Task
{
    private function __construct(
        private readonly ?TaskId      $id,     // null until persisted
        private readonly int          $userId,
        private string                $title,
        private ?string               $description,
        private TaskStatus            $status,
        private TaskPriority          $priority,
        private ?DateTimeImmutable    $dueDate,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable     $updatedAt,
    ) {}

    // ---------- Factory methods ----------

    public static function create(
        int     $userId,
        string  $title,
        ?string $description   = null,
        string  $status        = TaskStatus::PENDING,
        string  $priority      = TaskPriority::MEDIUM,
        ?string $dueDate       = null,
    ): self {
        self::assertTitle($title);

        $now = new DateTimeImmutable();

        return new self(
            id:          null,          // ID is assigned by the repository on persist
            userId:      $userId,
            title:       trim($title),
            description: $description,
            status:      TaskStatus::fromString($status),
            priority:    TaskPriority::fromString($priority),
            dueDate:     $dueDate ? new DateTimeImmutable($dueDate) : null,
            createdAt:   $now,
            updatedAt:   $now,
        );
    }

    public static function reconstruct(
        int     $id,
        int     $userId,
        string  $title,
        ?string $description,
        string  $status,
        string  $priority,
        ?string $dueDate,
        string  $createdAt,
        string  $updatedAt,
    ): self {
        return new self(
            id:          new TaskId($id),
            userId:      $userId,
            title:       $title,
            description: $description,
            status:      TaskStatus::fromString($status),
            priority:    TaskPriority::fromString($priority),
            dueDate:     $dueDate ? new DateTimeImmutable($dueDate) : null,
            createdAt:   new DateTimeImmutable($createdAt),
            updatedAt:   new DateTimeImmutable($updatedAt),
        );
    }

    // ---------- Domain behaviour ----------

    public function update(
        ?string $title       = null,
        ?string $description = null,
        ?string $status      = null,
        ?string $priority    = null,
        ?string $dueDate     = null,
    ): void {
        if ($title !== null) {
            self::assertTitle($title);
            $this->title = trim($title);
        }

        if ($description !== null) {
            $this->description = $description;
        }

        if ($status !== null) {
            $next         = TaskStatus::fromString($status);
            $this->status = $this->status->transitionTo($next); // enforces domain rule
        }

        if ($priority !== null) {
            $this->priority = TaskPriority::fromString($priority);
        }

        if ($dueDate !== null) {
            $this->dueDate = $dueDate === '' ? null : new DateTimeImmutable($dueDate);
        }

        $this->updatedAt = new DateTimeImmutable();
    }

    public function complete(): void
    {
        $this->status    = TaskStatus::completed();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function isOwnedBy(int $userId): bool
    {
        return $this->userId === $userId;
    }

    public function isOverdue(): bool
    {
        return $this->dueDate !== null
            && $this->dueDate < new DateTimeImmutable('today')
            && ! $this->status->isCompleted();
    }

    // ---------- Getters ----------

    /**
     * Returns null when the task has not yet been persisted.
     * Always non-null after being returned from the repository.
     */
    public function id(): ?TaskId            { return $this->id; }
    public function userId(): int            { return $this->userId; }
    public function title(): string          { return $this->title; }
    public function description(): ?string   { return $this->description; }
    public function status(): TaskStatus     { return $this->status; }
    public function priority(): TaskPriority { return $this->priority; }
    public function dueDate(): ?DateTimeImmutable { return $this->dueDate; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
    public function updatedAt(): DateTimeImmutable { return $this->updatedAt; }

    // ---------- Invariants ----------

    private static function assertTitle(string $title): void
    {
        $trimmed = trim($title);
        if ($trimmed === '') {
            throw new InvalidArgumentException('Task title cannot be empty.');
        }
        if (mb_strlen($trimmed) > 200) {
            throw new InvalidArgumentException('Task title cannot exceed 200 characters.');
        }
    }
}
