<?php

namespace App\Providers;

use App\Events\TaskCreated;
use App\Listeners\TaskCreatedListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TaskCreated::class => [
            TaskCreatedListener::class,
        ]
    ];
}
