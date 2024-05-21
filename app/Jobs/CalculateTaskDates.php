<?php

namespace App\Jobs;

use App\Models\Task;
use App\Models\User;
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

        if (null === $assignee->working_hours_start || null === $assignee->working_hours_end) {
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

        // Adjust startAt to ensure it falls within working hours and skips weekends.
        $startAt = $this->adjustForWorkingHours(
            $startAt,
            $assignee,
        );

        // Adjust endAt to ensure it falls within working hours and skips weekends.
        $endAt = $this->calculateEndTime(
            $startAt,
            $assignee,
            $task->duration,
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
        User $assignee,
    ): Carbon {
        $workingHoursStart = Carbon::parse($assignee->working_hours_start);
        $workingHoursEnd = Carbon::parse($assignee->working_hours_end);

        $breakHoursStart = null !== $assignee->break_hours_start ? Carbon::parse($assignee->break_hours_start) : null;
        $breakHoursEnd =  null !== $assignee->break_hours_end ? Carbon::parse($assignee->break_hours_end) : null;

        // If the time falls outside working hours, adjust it.
        if (
            $time->hour < $workingHoursStart->hour ||
            ($time->hour === $workingHoursStart->hour && $time->minute < $workingHoursStart->minute)
        ) {
            $time->setHour($workingHoursStart->hour)->setMinute($workingHoursStart->minute);
        } elseif (
            $time->hour > $workingHoursEnd->hour ||
            ($time->hour === $workingHoursEnd->hour && $time->minute >= $workingHoursEnd->minute)
        ) {
            // Move to the next working day and reset to start of working hours.
            $time = $time->addDay()->setHour($workingHoursStart->hour)->setMinute($workingHoursStart->minute);
        }

        while ($time->isWeekend()) {
            $time = $time->addDay();
        }

        return $time;
    }

    private function calculateEndTime(
        Carbon $startAt,
        User $assignee,
        string $duration,
    ): Carbon {
        $workingHoursEnd = Carbon::parse($assignee->working_hours_end);

        $endAt = $startAt->clone();
        $taskDurationInMinutes = $startAt->diffInMinutes($endAt->clone()->add(new DateInterval($duration)));

        // Calculate remaining time in minutes for working day and adjust end date.
        while ($taskDurationInMinutes >= 0) {
            $endTime = $endAt->clone()->setHour($workingHoursEnd->hour)->setMinute($workingHoursEnd->minute);
            $remaining = $endAt->diffInMinutes($endTime);

            $taskDurationInMinutes -= $remaining;
            if ($taskDurationInMinutes < 0) {
                // If remaining greather that time minutes left to finish task then calculate real remainign.
                $remaining = $remaining + $taskDurationInMinutes;
            }

            // Add remainign and move to next day if needed.
            $endAt = $endAt->addMinutes($remaining);
            $endAt = $this->adjustForWorkingHours(
                $endAt,
                $assignee,
            );
        }

        return $endAt;
    }
}
