<?php

namespace App\Providers;

use App\Events\TaskCaptured;
use App\Listeners\TaskCapturedListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TaskCaptured::class => [
            TaskCapturedListener::class,
        ]
    ];
}
