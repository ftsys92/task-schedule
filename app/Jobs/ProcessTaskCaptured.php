<?php

namespace App\Jobs;

use App\Events\TaskDurationCalculated;
use App\Models\Task;
use App\Services\OpenAI\Contracts\OpenAIClient;
use DateTimeImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessTaskCaptured implements ShouldQueue
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
                if there is not enough information, give approximate estimation but much enough to complete in realistic timeframe.
                don't add any other words or characters. how much time approximately can take next task:\n\n
                Title: %s\nDescription: %s",
                $task->title,
                $task->description
            ),
        );

        $task->duration = $duration;
        $task->save();

        event(new TaskDurationCalculated(
            new DateTimeImmutable(),
            $this->taskId
        ));

        Log::info([
            'message' => sprintf('"%s" job has been handled', self::class),
            'queue' => $this->queue,
            'task_id' => $task->id,
        ]);
    }
}
