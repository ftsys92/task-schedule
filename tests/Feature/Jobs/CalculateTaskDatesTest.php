<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Jobs\CalculateTaskDates;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculateTaskDatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculates_start_date_and_end_date_for_a_task(): void
    {
        $user = User::factory()->create([
            'working_hours_start' => '09:00',
            'working_hours_end' => '18:00',
            'break_hours_start' => '12:00',
            'break_hours_end' => '13:00',
        ]);

        Task::factory()->create([
            'assignee_id' => $user->id,
            'status' => 'confirmed',
            'duration' => 'PT1H',
            'start_at' => '2024-04-30 09:00:47',
            'end_at' => '2024-04-30 10:00:47'
        ]);

        $task2 = Task::factory()->create([
            'assignee_id' => $user->id,
            'status' => 'confirmed',
            'duration' => 'PT2H30M',
        ]);

        $job = new CalculateTaskDates($task2->id);

        $job->handle();

        $task2 = $task2->fresh();

        self::assertEquals($user->id, $task2->assignee_id);
        self::assertEquals('2024-04-30 10:00:47', $task2->start_at);
        self::assertEquals('2024-04-30 12:30:47', $task2->end_at);
    }

    public function test_calculates_start_date_and_next_day_end_date_for_a_task(): void
    {
        $user = User::factory()->create([
            'working_hours_start' => '09:00',
            'working_hours_end' => '18:00',
            'break_hours_start' => '12:00',
            'break_hours_end' => '13:00',
        ]);

        Task::factory()->create([
            'assignee_id' => $user->id,
            'status' => 'confirmed',
            'duration' => 'PT1H',
            'start_at' => '2024-04-30 09:00:47',
            'end_at' => '2024-04-30 10:00:47'
        ]);

        $task2 = Task::factory()->create([
            'assignee_id' => $user->id,
            'status' => 'confirmed',
            'duration' => 'PT8H30M',
        ]);

        $job = new CalculateTaskDates($task2->id);

        $job->handle();

        $task2 = $task2->fresh();

        self::assertEquals($user->id, $task2->assignee_id);
        self::assertEquals('2024-04-30 10:00:47', $task2->start_at);
        self::assertEquals('2024-05-01 09:30:47', $task2->end_at);
    }
}
