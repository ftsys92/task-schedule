<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\OpenAI\Contracts\OpenAIClient as OpenAIClientContract;
use App\Services\OpenAI\OpenAIClient;
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
    }

    public function boot(): void
    {
    }
}
