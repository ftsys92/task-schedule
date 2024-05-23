<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'working_hours_start' => '09:00',
            'working_hours_end' => '18:00',
            'break_hours_start' => '12:00',
            'break_hours_end' => '13:00',
        ]);
    }
}
