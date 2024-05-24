<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Tasks;

use App\Services\OpenAI\Contracts\OpenAIClient;
use App\Services\Tasks\GptTaskEstimator;
use Mockery;
use PHPUnit\Framework\TestCase;

class GptTaskEstimatorTest extends TestCase
{
    public function test_estimates_task()
    {
        $openAIMock = Mockery::mock(OpenAIClient::class);
        $openAIMock
            ->shouldReceive('message')
            ->andReturn('PT1H');

        $estimator = new GptTaskEstimator($openAIMock);

        self::assertEquals('PT1H', $estimator->estimate('test task', 'task_notes'));
    }

    public function test_returns_null_when_unable_to_estimate()
    {
        $openAIMock = Mockery::mock(OpenAIClient::class);
        $openAIMock
            ->shouldReceive('message')
            ->andReturn(null);

        $estimator = new GptTaskEstimator($openAIMock);

        self::assertEquals(null, $estimator->estimate('test task', 'task_notes'));
    }

    public function test_returns_null_when_wrong_iso8601_is_presented()
    {
        $openAIMock = Mockery::mock(OpenAIClient::class);
        $openAIMock
            ->shouldReceive('message')
            ->andReturn('Wrong format');

        $estimator = new GptTaskEstimator($openAIMock);

        self::assertEquals(null, $estimator->estimate('test task', 'task_notes'));
    }
}
