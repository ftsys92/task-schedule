<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\OpenAI\Contracts\OpenAIClient as OpenAIClientContract;
use App\Services\OpenAI\OpenAIClient;
use App\Services\Tasks\CarbonTaskDatesCalculator;
use App\Services\Tasks\Contracts\TaskDatesCalculator;
use App\Services\Tasks\Contracts\TaskEstimator;
use App\Services\Tasks\GptTaskEstimator;
use Illuminate\Support\ServiceProvider;
use OpenAI;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(OpenAIClientContract::class, function ($app) {
            $apiKey = $app->config->get('services.openai.api_key');

            return new OpenAIClient(
                OpenAI::client($apiKey)
            );
        });

        $this->app->singleton(TaskEstimator::class, function ($app) {
            return new GptTaskEstimator($app->make(OpenAIClientContract::class));
        });

        $this->app->singleton(TaskDatesCalculator::class, CarbonTaskDatesCalculator::class);
    }

    public function boot(): void
    {
    }
}
