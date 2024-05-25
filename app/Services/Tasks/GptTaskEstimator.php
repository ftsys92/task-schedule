<?php

declare(strict_types=1);

namespace App\Services\Tasks;

use App\Services\OpenAI\Contracts\OpenAIClient;
use App\Services\Tasks\Contracts\TaskEstimator;
use DateInterval;

final class GptTaskEstimator implements TaskEstimator
{
    public function __construct(private OpenAIClient $openAIClient)
    {
    }

    public function estimate(string $title, ?string $notes): ?string
    {
        $duration = $this->openAIClient->message(
            'The GPT breakdowns task and estimates an approximate time required for a tasks to be compelted and returns a single duration string in ISO 8601 format.
            The responses adhere strictly to this format, ensuring consistent, accurate results.
            The GPT does not provide additional commentary or explanations, focusing solely on producing valid duration strings in ISO 8601 format for users.
            Minimal duration is 30 minutes.',
            sprintf(
                "Estimate task. Take into consederation task notes if they are not empty:\n\n
                Task: %s\n Task notes: %s",
                $title,
                $notes ?? ''
            ),
        );

        if (null === $duration) {
            return null;
        }

        try {
            new DateInterval($duration);

            return $duration;
        } catch (\Exception $e) {
            return null;
        }
    }
}
