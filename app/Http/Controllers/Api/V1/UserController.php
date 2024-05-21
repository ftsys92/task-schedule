<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class UserController
{
    public function index(): JsonResponse
    {
        return new JsonResponse(User::all());
    }

    public function show(User $user): JsonResponse
    {
        return new JsonResponse($user);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $email = $request->input('email');
        $password = $request->input('password');

        $user = new User();
        $user->email = $email;
        $user->password =  Hash::make($password);
        $user->save();

        return new JsonResponse($user, Response::HTTP_CREATED);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $name = $request->input('name');
        $workingHoursStart = $request->input('working_hours_start');
        $workingHoursEnd = $request->input('working_hours_end');

        $user->update([
            'name' => $name,
            'working_hours_start' => $workingHoursStart,
            'working_hours_end' => $workingHoursEnd,
        ]);

        return new JsonResponse($user);
    }

    public function delete(User $user): JsonResponse
    {
        $user->delete();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
