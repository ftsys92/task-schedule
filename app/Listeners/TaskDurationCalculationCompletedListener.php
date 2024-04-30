<?php

namespace App\Listeners;

use App\Events\TaskDurationCalculationCompleted;
use App\Jobs\CalculateTaskDates;
use Illuminate\Support\Facades\Log;

class TaskDurationCalculationCompletedListener
{
    public function __construct()
    {
    }

    public function handle(TaskDurationCalculationCompleted $event): void
    {

    }
}
