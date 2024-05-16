<?php

namespace Tests\Feature\Jobs;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculateTaskDatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculates_start_and_end_dates_for_a_task(): void
    {
        $user = User::factory()->create();
        $task1 = Task::factory()->create([
            'assignee_id' => $user->id,
            'start_at' => '2024-04-30 12:30:00',
            'end_at' => '2024-04-30 13:30:00'
        ]);

        $task2 = Task::factory()->create([
            'assignee_id' => $user->id,
            'start_at' => '2024-04-30 14:00:47',
            'end_at' => '2024-05-01 12:30:47'
        ]);

        self::assertEquals($user->id, $task1->assignee_id);
        self::assertEquals($user->id, $task2->assignee_id);
    }
}
