<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->title(),
            'notes' => fake()->text(),
            'duration' => 'PT1H',
            'assignee_id' => User::factory()->create()->id,
            'status' => fake()->randomElement(Task::STATUSES)
        ];
    }
}
