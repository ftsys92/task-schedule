<?php

namespace App\Listeners;

use App\Events\TaskDurationCalculationCompleted;
use Illuminate\Support\Facades\Log;

class TaskDurationCalculationCompletedListener
{
    public function __construct()
    {
    }

    public function handle(TaskDurationCalculationCompleted $event): void
    {
        Log::info('TaskDurationCalculationCompletedListener');
    }
}
