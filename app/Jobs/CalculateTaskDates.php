<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Task;
use App\Services\Tasks\Contracts\TaskDatesCalculator;
use Carbon\Carbon;
use DateInterval;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CalculateTaskDates implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $taskId)
    {
    }

    public function handle(TaskDatesCalculator $taskDatesCalculator): void
    {
        $task = Task::findOrFail($this->taskId);

        if (null !== $task->start_at && null !== $task->end_at) {
            return;
        }

        $assignee = $task->assignee()->first();

        if (
            null === $assignee ||
            (null === $assignee->working_hours_start || null === $assignee->working_hours_end) ||
            (null === $assignee->break_hours_start || null === $assignee->break_hours_end)
        ) {
            return;
        }

        $lastTask = $assignee
            ->tasks()
            ->doable()
            ->whereNotNull('end_at')
            ->orderBy('end_at', 'desc')
            ->first();

        // Start at the end time of the last task or from now
        $startAt = null !== $lastTask && $lastTask->end_at
            ? $lastTask->end_at
            : Carbon::now();

        $timeline = [
            [
                'start' => $assignee->working_hours_start,
                'end' => $assignee->break_hours_start,
            ],
            [
                'start' => $assignee->break_hours_end,
                'end' => $assignee->working_hours_end,
            ],
        ];

        $dates = $taskDatesCalculator->calculateDates($startAt, new DateInterval($task->duration), $timeline);

        $task->start_at = $dates->getStartDate();
        $task->end_at = $dates->getEndDate();
        $task->save();
    }
}
