<?php

namespace App\Events;

use DateTimeImmutable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TaskCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public DateTimeImmutable $at,
        public int $taskId
    ) {
        Log::info('TaskCreated');
    }
}
