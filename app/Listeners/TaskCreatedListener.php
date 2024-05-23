<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\TaskCreated;
use App\Jobs\CalculateTaskDuration;
use Illuminate\Support\Facades\Log;

class TaskCreatedListener
{
    public function __construct()
    {
    }

    public function handle(TaskCreated $event): void
    {
        Log::info('TaskCreatedListener');
        CalculateTaskDuration::dispatch($event->taskId);
    }
}
