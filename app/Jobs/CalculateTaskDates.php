<?php

namespace App\Jobs;

use App\Events\TaskDurationCalculationCompleted;
use App\Events\TaskDurationCalculationFailed;
use App\Models\Task;
use App\Services\OpenAI\Contracts\OpenAIClient;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use DateInterval;
use DateTimeImmutable;
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
            ->double()
            ->whereNotNull('end_at')
            ->orderBy('end_at', 'desc')
            ->first();

        $startAt = null !== $lastTask && $lastTask->end_at ?
            $lastTask->end_at->add(CarbonInterval::fromString('30 minutes')) :
            Carbon::now()->add('30 minutes');
        $endAt = $startAt->clone()->add(CarbonInterval::fromString($task->duration));

        $task->start_at = $startAt;
        $task->end_at = $endAt;

        $task->save();

        Log::info([
            'message' => sprintf('"%s" job has been handled', self::class),
            'queue' => $this->queue,
            'task_id' => $task->id,
        ]);
    }
}
