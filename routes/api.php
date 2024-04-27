<?php

use App\Http\Controllers\Api\V1\RoutineController;
use App\Http\Controllers\Api\V1\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/routines/{routine}/tasks', [TaskController::class, 'index']);
Route::get('/routines/{routine}/tasks/{task}', [TaskController::class, 'show']);
Route::post('/routines/{routine}/tasks', [TaskController::class, 'store']);
Route::delete('/routines/{routine}/tasks/{task}', [TaskController::class, 'delete']);

Route::get('/routines', [RoutineController::class, 'index']);
Route::get('/routines/{routine}', [RoutineController::class, 'show']);
Route::post('/routines', [RoutineController::class, 'store']);
Route::delete('/routines/{routine}', [RoutineController::class, 'delete']);
