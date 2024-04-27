<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\RoutineUpdated;
use App\Events\TaskCreated;
use App\Http\Requests\StoreTaskRequest;
use App\Models\Routine;
use App\Models\Task;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class TaskController
{
    public function index(): JsonResponse
    {
        return new JsonResponse(Task::all());
    }

    public function store(Routine $routine, StoreTaskRequest $request): JsonResponse
    {
        $title = $request->input('title');
        $notes = $request->input('notes');

        $task = $routine->tasks()->create([
            'title' => $title,
            'notes' => $notes,
            'status' => Task::STATUS_CREATED,
        ]);

        event(new TaskCreated(
            $task->created_at->toImmutable(),
            $routine->id,
            $task->id,
        ));

        return new JsonResponse($task, Response::HTTP_CREATED);
    }

    public function show(Routine $routine, Task $task): JsonResponse
    {
        return new JsonResponse($task);
    }

    public function delete(Routine $routine, Task $task): JsonResponse
    {
        $task->delete();

        event(new RoutineUpdated(
            new DateTimeImmutable(),
            $routine->id,
        ));

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
