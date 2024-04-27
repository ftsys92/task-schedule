<?php

use App\Http\Controllers\Api\V1\RoutineController;
use App\Http\Controllers\Api\V1\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/tasks', [TaskController::class, 'index']);
Route::post('/tasks', [TaskController::class, 'store']);
Route::delete('/tasks/{task}', [TaskController::class, 'delete']);

Route::get('/routines', [RoutineController::class, 'index']);
Route::post('/routines', [RoutineController::class, 'store']);
Route::delete('/routines/{routine}', [RoutineController::class, 'delete']);
