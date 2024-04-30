<?php

namespace App\Jobs;

use App\Models\Task;
use Carbon\Carbon;
use Carbon\CarbonInterval;
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

    public function __construct(public string $taskId)
    {
    }

    public function handle(): void
    {
        $task = Task::findOrFail($this->taskId);

        if (null !== $task->start_at && null !== $task->end_at) {
            return;
        }

        $assignee = $task->assignee()->first();
        $lastTask = $assignee
            ->tasks()
            ->doable()
            ->whereNotNull('end_at')
            ->orderBy('end_at', 'desc')
            ->first();


        $workingHoursStart = Carbon::parse('01:30 pm');
        $workingHoursEnd = Carbon::parse('03:30 pm');

        // Start at the end time of the last task or 30 minutes from now
        $startAt = null !== $lastTask && $lastTask->end_at
            ? $lastTask->end_at
            : Carbon::now();

        // Adjust startAt to ensure it falls within working hours and skips weekends
        $startAt = $this->adjustForWorkingHours(
            $startAt,
            $workingHoursStart->hour,
            $workingHoursStart->minute,
            $workingHoursEnd->hour,
            $workingHoursEnd->minute,
        );

        // Adjust endAt to ensure it falls within working hours and skips weekends
        $endAt = $this->calculateEndTime(
            $startAt,
            $task->duration,
            $workingHoursStart->hour,
            $workingHoursStart->minute,
            $workingHoursEnd->hour,
            $workingHoursEnd->minute,
        );

        $task->start_at = $startAt;
        $task->end_at = $endAt;

        $task->save();

        Log::info([
            'message' => sprintf('"%s" job has been handled', self::class),
            'queue' => $this->queue,
            'task_id' => $task->id,
        ]);
    }

    private function adjustForWorkingHours(
        Carbon $time,
        int $workingHoursStartHour,
        int $workingHoursStartMinute,
        int $workingHoursEndHour,
        int $workingHoursEndMinute,
    ): Carbon {
        // If the time falls outside working hours, adjust it
        if ($time->hour < $workingHoursStartHour || ($time->hour === $workingHoursStartHour && $time->minute < $workingHoursStartMinute)) {
            $time->setHour($workingHoursStartHour)->setMinute($workingHoursStartMinute);
        } elseif ($time->hour > $workingHoursEndHour || ($time->hour === $workingHoursEndHour && $time->minute >= $workingHoursEndMinute)) {
            // Move to the next working day and reset to start of working hours
            $time = $time->addDay()->setHour($workingHoursStartHour)->setMinute($workingHoursStartMinute);
        }

        // Skip weekends when adjusting for the next working day
        while ($time->isWeekend()) {
            $time = $time->addDay();
        }

        return $time;
    }

    private function calculateEndTime(
        Carbon $startAt,
        string $duration,
        int $workingHoursStartHour,
        int $workingHoursStartMinute,
        int $workingHoursEndHour,
        int $workingHoursEndMinute,
    ): Carbon {
        // Add task duration to calculate endAt
        $endTime = $startAt->clone()->setHour($workingHoursEndHour)->setMinute($workingHoursEndMinute);
        $endAt = $startAt->clone()->add(new DateInterval($duration));

        $diff = $endAt->diffInMinutes($endTime);

        if ($diff < 0) {
            $endAt = $endAt->addDay()->setHour($workingHoursStartHour)->setMinute($workingHoursStartMinute);
            $endAt = $endAt->addMinutes(abs($diff));
        }

        // Skip weekends when adjusting for the next working day
        while ($endAt->isWeekend()) {
            $endAt = $endAt->addDay();
        }

        return $endAt;
    }
}
