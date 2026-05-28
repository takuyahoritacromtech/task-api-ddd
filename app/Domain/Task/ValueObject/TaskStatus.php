<?php

declare(strict_types=1);

namespace App\Domain\Task\ValueObject;

use InvalidArgumentException;

/**
 * Value Object — Task Status.
 *
 * Encapsulates allowed status transitions.
 * Domain rule: a completed task cannot be moved back to pending.
 */
final class TaskStatus
{
    public const PENDING     = 'pending';
    public const IN_PROGRESS = 'in_progress';
    public const COMPLETED   = 'completed';

    private const VALID = [self::PENDING, self::IN_PROGRESS, self::COMPLETED];

    public function __construct(private readonly string $value)
    {
        if (! in_array($value, self::VALID, true)) {
            throw new InvalidArgumentException(
                "Invalid status '{$value}'. Allowed: " . implode(', ', self::VALID)
            );
        }
    }

    public static function pending(): self     { return new self(self::PENDING); }
    public static function inProgress(): self  { return new self(self::IN_PROGRESS); }
    public static function completed(): self   { return new self(self::COMPLETED); }
    public static function fromString(string $value): self { return new self($value); }

    public function value(): string { return $this->value; }

    public function isCompleted(): bool { return $this->value === self::COMPLETED; }

    /**
     * Domain rule: once completed, status cannot regress to pending.
     *
     * @throws InvalidArgumentException
     */
    public function transitionTo(self $next): self
    {
        if ($this->isCompleted() && $next->value === self::PENDING) {
            throw new InvalidArgumentException('Cannot revert a completed task to pending.');
        }

        return $next;
    }

    public function equals(self $other): bool { return $this->value === $other->value; }

    public function __toString(): string { return $this->value; }
}
