<?php

namespace App\Providers;

use App\Services\OpenAI\Contracts\OpenAIClient as OpenAIClientContract;
use App\Services\OpenAI\OpenAIClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use OpenAI;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(OpenAIClientContract::class, function ($app) {
            $api_key = $app->config->get('services.openai.api_key');

            return new OpenAIClient(
                OpenAI::client($api_key)
            );
        });
    }

    public function boot(): void
    {
    }
}
