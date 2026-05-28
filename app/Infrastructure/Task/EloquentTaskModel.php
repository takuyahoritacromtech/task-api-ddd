<?php

declare(strict_types=1);

namespace App\Infrastructure\Task;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Eloquent model — Infrastructure concern only.
 *
 * This class is NOT used in the Domain or Application layers.
 * Domain entities are reconstructed from this model via the Repository.
 */
final class EloquentTaskModel extends Model
{
    use SoftDeletes;

    protected $table    = 'tasks';
    protected $fillable = [
        'user_id', 'title', 'description',
        'status', 'priority', 'due_date',
    ];
    protected $casts = ['due_date' => 'date'];
}
