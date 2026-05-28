<?php

declare(strict_types=1);

namespace App\Domain\Task\ValueObject;

use InvalidArgumentException;

/**
 * Value Object — Task Identity.
 *
 * Immutable wrapper around an integer ID.
 * Guarantees a positive integer; prevents raw ints from leaking
 * into domain logic without intent.
 */
final class TaskId
{
    public function __construct(private readonly int $value)
    {
        if ($value <= 0) {
            throw new InvalidArgumentException("TaskId must be a positive integer, got {$value}.");
        }
    }

    public function value(): int
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
