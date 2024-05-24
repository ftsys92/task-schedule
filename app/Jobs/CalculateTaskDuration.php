<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Events\TaskDurationCalculationCompleted;
use App\Events\TaskDurationCalculationFailed;
use App\Models\Task;
use App\Services\Tasks\Contracts\TaskEstimator;
use DateTimeImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculateTaskDuration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $taskId)
    {
    }

    public function handle(TaskEstimator $estimator): void
    {
        $task = Task::findOrFail($this->taskId);
        $duration = $estimator->estimate($task->title, $task->notes);

        if (null === $duration) {
            event(new TaskDurationCalculationFailed(
                new DateTimeImmutable(),
                $this->taskId,
            ));

            return;
        }

        $duration = $duration;
        $task->duration = $duration;
        $task->status = Task::STATUS_PENDING;
        $task->save();

        event(new TaskDurationCalculationCompleted(
            new DateTimeImmutable(),
            $this->taskId,
            $duration
        ));
    }
}
