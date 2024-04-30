<?php

namespace App\Jobs;

use App\Events\TaskDurationCalculationCompleted;
use App\Events\TaskDurationCalculationFailed;
use App\Models\Task;
use App\Services\OpenAI\Contracts\OpenAIClient;
use DateInterval;
use DateTimeImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CalculateTaskDuration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $taskId)
    {
    }

    public function handle(OpenAIClient $openAIClient): void
    {
        $task = Task::findOrFail($this->taskId);

        $duration = $openAIClient->message(
            'give a short answer as a valid PHP DateInterval string.
            if there is not enough information, give medium long estimation that will be enough to clarify details.
            do not add any other words or characters. make sure you follow format required. try to do not overestimate.',
            sprintf(
                "How much time approximately can take next task(minimum is 30 minutes):\n\n
                Title: %s\n Notes: %s",
                $task->title,
                $task->notes
            ),
        );

        $isValid = self::isValidInterval($duration);

        if (!$isValid) {
            event(new TaskDurationCalculationFailed(
                new DateTimeImmutable(),
                $this->taskId,
            ));

            return;
        }

        $task->duration = $duration;
        $task->status = Task::STATUS_PENDING;
        $task->save();

        event(new TaskDurationCalculationCompleted(
            new DateTimeImmutable(),
            $this->taskId,
            $duration
        ));

        Log::info([
            'message' => sprintf('"%s" job has been handled', self::class),
            'queue' => $this->queue,
            'task_id' => $task->id,
        ]);
    }

    private static function isValidInterval(string $interval): bool
    {
        try {
            $dateInterval =  new DateInterval($interval);
            return $dateInterval !== false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
