<?php

namespace App\Listeners;

use App\Events\TaskDurationCalculationFailed;
use Illuminate\Support\Facades\Log;

class TaskDurationCalculationFailedListener
{
    public function __construct()
    {
    }

    public function handle(TaskDurationCalculationFailed $event): void
    {
        Log::info('TaskDurationCalculationFailedListener');
    }
}
