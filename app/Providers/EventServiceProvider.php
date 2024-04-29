<?php

namespace App\Providers;

use App\Events\TaskCreated;
use App\Events\TaskDurationCalculationCompleted;
use App\Listeners\TaskCreatedListener;
use App\Listeners\TaskDurationCalculationCompletedListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TaskCreated::class => [
            TaskCreatedListener::class,
        ],
        TaskDurationCalculationCompleted::class => [
            TaskDurationCalculationCompletedListener::class,
        ]
    ];
}
