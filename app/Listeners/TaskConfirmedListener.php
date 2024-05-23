<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\TaskConfirmed;
use App\Jobs\CalculateTaskDates;

class TaskConfirmedListener
{
    public function __construct()
    {
    }

    public function handle(TaskConfirmed $event): void
    {
        CalculateTaskDates::dispatch(
            $event->taskId,
        );
    }
}
