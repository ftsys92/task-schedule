<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\TaskConfirmed;
use App\Events\TaskCreated;
use App\Listeners\TaskConfirmedListener;
use App\Listeners\TaskCreatedListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TaskCreated::class => [
            TaskCreatedListener::class,
        ],
        TaskConfirmed::class => [
            TaskConfirmedListener::class,
        ]
    ];
}
