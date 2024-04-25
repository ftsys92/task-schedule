<?php

namespace App\Listeners;

use App\Events\TaskCaptured;
use App\Jobs\ProcessTaskCaptured;
use Illuminate\Support\Facades\Log;

class TaskCapturedListener
{
    public function __construct()
    {
    }

    public function handle(TaskCaptured $event): void
    {
        Log::info('TaskCapturedListener');
        ProcessTaskCaptured::dispatch(
            $event->taskId,
        );
    }
}
