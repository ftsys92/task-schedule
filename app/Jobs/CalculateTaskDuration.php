<?php

namespace App\Jobs;

use App\Events\TaskDurationCalculated;
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
            sprintf(
                "give a short answer in the exact formats \"X hours\" or \"X days\".
                if there is not enough information, give minimal estimation that will be enough to claarify details.
                don't add any other words or characters. make sure you follow format required.\n\n
                how much time approximately can take next task:\n\n
                Title: %s\nDescription: %s",
                $task->title,
                $task->description
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
        $task->save();

        event(new TaskDurationCalculated(
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

    private static function isValidInterval(string $interval): ?string
    {
        try {
            $dateInterval = DateInterval::createFromDateString($interval);
            return $dateInterval !== false ? $interval : null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
