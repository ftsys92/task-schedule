<?php

namespace Tests\Feature\Listeners;

use App\Events\UserCaptured;
use App\Jobs\ProcessUserCaptured;
use App\Listeners\UserCapturedListener;
use App\Models\User;
use Faker\Generator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Mockery\MockInterface;
use Tests\TestCase;

class UserCapturedListenerTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispatches_process_user_capture_job(): void
    {

        Queue::fake();

        $email = 'acme@qwerty.xyz';
        $user = User::factory()->create([
            'email' => $email,
            'name' => null,
        ]);

        (new UserCapturedListener())->handle(
            new UserCaptured($user->created_at->toImmutable(), $user->id)
        );

        Queue::assertPushed(ProcessUserCaptured::class, 1);

        $jobs = collect(Queue::pushedJobs());
        $job = $jobs->flatten()->first();

        self::assertEquals($user->id, $job->userId);
    }
}
