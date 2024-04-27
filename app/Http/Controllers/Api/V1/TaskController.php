<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\TaskCreated;
use App\Http\Requests\StoreTaskRequest;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class TaskController
{
    public function index(): JsonResponse
    {
        return new JsonResponse(Task::all());
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $title = $request->input('title');
        $notes = $request->input('notes');

        $task = Task::create([
            'title' => $title,
            'notes' => $notes,
            'status' => Task::STATUS_CREATED,
        ]);

        event(new TaskCreated(
            $task->created_at->toImmutable(),
            $task->id,
        ));

        return new JsonResponse([
            'id' => $task->id,
            'title' => $task->title,
            'notes' => $task->notes,
        ], Response::HTTP_CREATED);
    }

    public function delete(Task $task): JsonResponse
    {
        $task->delete();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
