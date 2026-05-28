<?php

declare(strict_types=1);

namespace App\Domain\Task\ValueObject;

use InvalidArgumentException;

/**
 * Value Object — Task Priority.
 */
final class TaskPriority
{
    public const LOW    = 'low';
    public const MEDIUM = 'medium';
    public const HIGH   = 'high';

    private const VALID  = [self::LOW, self::MEDIUM, self::HIGH];
    private const WEIGHT = [self::LOW => 1, self::MEDIUM => 2, self::HIGH => 3];

    public function __construct(private readonly string $value)
    {
        if (! in_array($value, self::VALID, true)) {
            throw new InvalidArgumentException(
                "Invalid priority '{$value}'. Allowed: " . implode(', ', self::VALID)
            );
        }
    }

    public static function fromString(string $value): self { return new self($value); }
    public static function medium(): self { return new self(self::MEDIUM); }

    public function value(): string { return $this->value; }

    public function isHigherThan(self $other): bool
    {
        return self::WEIGHT[$this->value] > self::WEIGHT[$other->value];
    }

    public function equals(self $other): bool { return $this->value === $other->value; }

    public function __toString(): string { return $this->value; }
}
