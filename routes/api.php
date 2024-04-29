<?php

use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\UserTaskController;
use Illuminate\Support\Facades\Route;

Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{user}', [UserController::class, 'show']);
Route::post('/users', [UserController::class, 'store']);
Route::delete('/users/{user}', [UserController::class, 'delete']);

Route::get('/users/{user}/tasks', [UserTaskController::class, 'index']);
Route::get('/users/{user}/tasks/{task}', [UserTaskController::class, 'show']);
Route::post('/users/{user}/tasks', [UserTaskController::class, 'store']);
Route::delete('/users/{user}/tasks/{task}', [UserTaskController::class, 'delete']);
