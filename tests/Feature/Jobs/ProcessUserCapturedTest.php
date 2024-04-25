<?php

namespace Tests\Feature\Jobs;

use App\Jobs\ProcessUserCaptured;
use App\Models\User;
use Faker\Generator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class ProcessUserCapturedTest extends TestCase
{
    use RefreshDatabase;

    public function test_adds_name_for_user(): void
    {
        $this->mock(Generator::class, function (MockInterface $mock) {
            return $mock->shouldReceive('name')->andReturn('Acme Bar');
        });

        $email = 'acme@qwerty.xyz';
        $user = User::factory()->create([
            'email' => $email,
            'name' => null,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => $email,
            'name' => null,
        ]);

        (new ProcessUserCaptured($user->id))->handle($this->app->make(Generator::class));

        $this->assertDatabaseHas('users', [
            'email' => $email,
            'name' => 'Acme Bar',
        ]);
    }
}
