<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\TaskDurationCalculationCompleted;

class TaskDurationCalculationCompletedListener
{
    public function __construct()
    {
    }

    public function handle(TaskDurationCalculationCompleted $event): void
    {

    }
}
