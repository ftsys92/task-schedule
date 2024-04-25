<?php

namespace App\Listeners;

use App\Events\TaskDurationCalculated;
use Illuminate\Support\Facades\Log;

class TaskDurationCalculatedListener
{
    public function __construct()
    {
    }

    public function handle(TaskDurationCalculated $event): void
    {
        Log::info('TaskDurationCalculatedListener');
    }
}
