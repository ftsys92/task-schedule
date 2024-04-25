<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\TaskCaptured;
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
        $description = $request->input('description');

        $task = Task::create([
            'title' => $title,
            'description' => $description,
        ]);

        event(new TaskCaptured(
            $task->created_at->toImmutable(),
            $task->id,
        ));

        return new JsonResponse([
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
        ], Response::HTTP_CREATED);
    }

    public function delete(Task $task): JsonResponse
    {
        $task->delete();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
