<?php

namespace Tests\Feature\Jobs;

use App\Jobs\CalculateTaskDates;
use App\Jobs\ProcessUserCaptured;
use App\Models\Task;
use App\Models\User;
use Faker\Generator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class CalculateTaskDatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculates_start_and_end_dates_for_a_task(): void
    {
        $user = User::factory()->create();
        $task1 = Task::factory()->create([
            'title' => 'go for meals for 1 hour',
            'duration' => '1 hour',
            'assignee_id' => $user->id,
            'start_at' => '2024-04-30 12:30:00',
            'end_at' => '2024-04-30 13:30:00'
        ]);

        $task2 = Task::factory()->create([
            'title' => 'go for meals for 6 hours',
            'duration' => '6 hour',
            'assignee_id' => $user->id,
            'start_at' => '2024-04-30 14:00:47',
            'end_at' => '2024-05-01 12:30:47'
        ]);

        $task3 = Task::factory()->create([
            'title' => 'go for meals for 6 hours',
            'duration' => '6 hour',
            'assignee_id' => $user->id,
        ]);

        (new CalculateTaskDates($user->id))->handle($this->app->make(Generator::class));
    }
}
