<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Application\Task\Create\CreateTaskCommand;
use App\Application\Task\Create\CreateTaskHandler;
use App\Application\Task\Delete\DeleteTaskCommand;
use App\Application\Task\Delete\DeleteTaskHandler;
use App\Application\Task\Query\ListTasksHandler;
use App\Application\Task\Query\ListTasksQuery;
use App\Application\Task\Update\UpdateTaskCommand;
use App\Application\Task\Update\UpdateTaskHandler;
use App\Domain\Task\Exception\TaskNotFoundException;
use App\Presentation\Http\Requests\StoreTaskRequest;
use App\Presentation\Http\Requests\UpdateTaskRequest;
use App\Presentation\Http\Resources\TaskResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

/**
 * Presentation Controller — thin layer.
 *
 * Responsibilities:
 *   1. Parse HTTP request into a Command/Query
 *   2. Dispatch to Application handler
 *   3. Format the response
 *
 * No business logic here.
 */
final class TaskController extends Controller
{
    public function __construct(
        private readonly CreateTaskHandler $createHandler,
        private readonly UpdateTaskHandler $updateHandler,
        private readonly DeleteTaskHandler $deleteHandler,
        private readonly ListTasksHandler  $listHandler,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $result = $this->listHandler->handle(new ListTasksQuery(
            userId:   $request->user()->id,
            perPage:  (int) ($request->query('per_page', 15)),
            page:     (int) ($request->query('page', 1)),
            status:   $request->query('status'),
            priority: $request->query('priority'),
            sort:     $request->query('sort', 'created_at'),
            order:    $request->query('order', 'desc'),
        ));

        return TaskResource::collection($result['data'])
            ->additional(['meta' => ['total' => $result['total']]]);
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $this->createHandler->handle(new CreateTaskCommand(
            userId:      $request->user()->id,
            title:       $request->validated('title'),
            description: $request->validated('description'),
            status:      $request->validated('status', 'pending'),
            priority:    $request->validated('priority', 'medium'),
            dueDate:     $request->validated('due_date'),
        ));

        return (new TaskResource($task))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateTaskRequest $request, int $id): TaskResource
    {
        try {
            $task = $this->updateHandler->handle(new UpdateTaskCommand(
                taskId:      $id,
                userId:      $request->user()->id,
                title:       $request->validated('title'),
                description: $request->validated('description'),
                status:      $request->validated('status'),
                priority:    $request->validated('priority'),
                dueDate:     $request->validated('due_date'),
            ));
        } catch (TaskNotFoundException $e) {
            abort(404, $e->getMessage());
        }

        return new TaskResource($task);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $this->deleteHandler->handle(new DeleteTaskCommand(
                taskId: $id,
                userId: $request->user()->id,
            ));
        } catch (TaskNotFoundException $e) {
            abort(404, $e->getMessage());
        }

        return response()->json(['message' => 'Task deleted.']);
    }
}
