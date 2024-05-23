<?php

declare(strict_types=1);

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

    public function __construct(public int $taskId)
    {
    }

    public function handle(OpenAIClient $openAIClient): void
    {
        $task = Task::findOrFail($this->taskId);

        $duration = $openAIClient->message(
            'The GPT estimates the time required for tasks and returns a single duration string in ISO 8601 format.
            The responses adhere strictly to this format, ensuring consistent, accurate results.
            The GPT does not provide additional commentary or explanations, focusing solely on producing valid duration strings for users.
            Minimal duration is 30 minutes.',
            sprintf(
                "Estimate task. Take into consederation task notes if they are not empty:\n\n
                Task: %s\n Task notes: %s",
                $task->title,
                $task->notes
            ),
        );

        $isValid = is_string($duration) && self::isValidInterval($duration);

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
