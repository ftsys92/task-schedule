<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\TaskConfirmed;
use App\Events\TaskCreated;
use App\Http\Requests\StoreTaskRequest;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserTaskController
{
    public function index(User $user, Request $request): JsonResponse
    {
        $tasks = $user->tasks();
        $request->whenHas('status', function () use ($tasks, $request) {
            $tasks->where('status', $request->input('status'));
        });

        $tasks->orderBy('start_at', 'asc')->orderBy('id', 'asc');

        return new JsonResponse($tasks->get());
    }

    public function store(User $user, StoreTaskRequest $request): JsonResponse
    {
        $title = $request->input('title');
        $notes = $request->input('notes');
        $start_at = $request->input('start_at');
        $end_at = $request->input('end_at');

        $task = $user->tasks()->create([
            'title' => $title,
            'notes' => $notes,
            'status' => Task::STATUS_CREATED,
            'start_at' => $start_at,
            'end_at' => $end_at,
        ]);

        event(new TaskCreated(
            $task->created_at->toImmutable(),
            $user->id,
            $task->id,
        ));

        return new JsonResponse($task, Response::HTTP_CREATED);
    }

    public function confirm(User $user, Task $task): JsonResponse
    {
        $task->status = Task::STATUS_CONFIRMED;

        $task->save();

        event(new TaskConfirmed(
            $task->created_at->toImmutable(),
            $user->id,
            $task->id,
        ));

        return new JsonResponse($task, Response::HTTP_CREATED);
    }

    public function show(User $user, Task $task): JsonResponse
    {
        return new JsonResponse($task);
    }

    public function delete(User $user, Task $task): JsonResponse
    {
        $task->delete();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
