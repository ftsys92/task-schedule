<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\RoutineCreated;
use App\Http\Requests\StoreRoutineRequest;
use App\Models\Routine;;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class RoutineController
{
    public function index(): JsonResponse
    {
        return new JsonResponse(Routine::all());
    }

    public function store(StoreRoutineRequest $request): JsonResponse
    {
        $name = $request->input('name');
        $start_at = $request->input('start_at');
        $end_at = $request->input('end_at');

        $routine = Routine::create([
            'name' => $name,
            'start_at' => $start_at,
            'end_at' => $end_at,
        ]);

        event(new RoutineCreated(
            $routine->created_at->toImmutable(),
            $routine->id,
        ));

        return new JsonResponse($routine, Response::HTTP_CREATED);
    }

    public function delete(Routine $routine): JsonResponse
    {
        $routine->delete();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
