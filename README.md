# Task API — DDD Architecture

A Task Management REST API built with **Laravel 11** following **Domain-Driven Design (DDD)** and **CQRS** principles.

Compare with [task-api](https://github.com/takuyahoritacromtech/task-api) (MVC version) to see architectural trade-offs.

## Architecture Overview

```
app/
├── Domain/              # Pure PHP — NO framework dependency
│   └── Task/
│       ├── Entity/      # Task entity with business rules
│       ├── ValueObject/ # TaskId, TaskStatus, TaskPriority (immutable)
│       ├── Repository/  # Interface — defines WHAT we need
│       └── Exception/   # Domain-specific exceptions
│
├── Application/         # Use-case orchestration (CQRS)
│   └── Task/
│       ├── Create/      # CreateTaskCommand + CreateTaskHandler
│       ├── Update/      # UpdateTaskCommand + UpdateTaskHandler
│       ├── Delete/      # DeleteTaskCommand + DeleteTaskHandler
│       └── Query/       # ListTasksQuery + ListTasksHandler
│
├── Infrastructure/      # Framework & persistence details
│   └── Task/
│       ├── EloquentTaskModel.php       # Eloquent (infrastructure only)
│       └── EloquentTaskRepository.php  # Implements domain interface
│
└── Presentation/        # HTTP layer — thin controllers only
    └── Http/
        ├── Controllers/TaskController.php
        ├── Requests/    # Laravel Form Requests
        └── Resources/   # API JSON formatting
```

## Key DDD Concepts Demonstrated

### Value Objects (immutable)

```php
$status = TaskStatus::fromString('pending');
$next   = $status->transitionTo(TaskStatus::completed()); // domain rule enforced

// Domain rule: completed → pending is forbidden
$status = TaskStatus::completed();
$status->transitionTo(TaskStatus::pending()); // throws InvalidArgumentException
```

### Domain Entity (no framework)

```php
// Task entity knows its own business rules
$task = Task::create(userId: 1, title: 'Ship it', priority: 'high');
$task->complete();       // changes status + updatedAt
$task->isOverdue();      // pure domain logic
$task->isOwnedBy(2);    // ownership check
```

### CQRS (Commands & Queries separated)

```php
// Command — intent to change state
$handler->handle(new CreateTaskCommand(userId: 1, title: 'Write tests', priority: 'high'));

// Query — read-only, no side effects
$result = $handler->handle(new ListTasksQuery(userId: 1, status: 'pending'));
```

### Repository Interface in Domain

```php
// Domain defines the contract
interface TaskRepositoryInterface {
    public function save(Task $task): Task;
    public function findByIdAndUserId(TaskId $id, int $userId): ?Task;
    // ...
}

// Infrastructure provides the implementation (Eloquent)
class EloquentTaskRepository implements TaskRepositoryInterface { ... }
```

## Tech Stack

| Layer          | Technology                   |
|----------------|------------------------------|
| Framework      | Laravel 11                   |
| Auth           | Laravel Sanctum              |
| Database       | MySQL 8                      |
| Architecture   | DDD + CQRS                   |
| Unit Tests     | PHPUnit 11 (pure domain, no DB) |
| Container      | Docker + Docker Compose      |

## Quick Start

```bash
git clone https://github.com/takuyahoritacromtech/task-api-ddd.git
cd task-api-ddd

cp .env.example .env
docker compose up -d
docker compose exec app php artisan migrate --seed
```

## Running Tests

```bash
# Domain unit tests — no database needed, runs in ~15ms
php vendor/bin/phpunit tests/Unit/ --bootstrap vendor/autoload.php

# All tests (requires database)
php artisan test
```

## API

Same endpoints as [task-api](https://github.com/takuyahoritacromtech/task-api#api-reference). See that README for full API documentation.

## License

MIT
