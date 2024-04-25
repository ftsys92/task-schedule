<?php

namespace App\Services\OpenAI;

use App\Services\OpenAI\Contracts\OpenAIClient as OpenAIClientContract;
use OpenAI\Client;

final class OpenAIClient implements OpenAIClientContract
{
    public function __construct(
        private Client $client,
    ) {
    }

    public function message(string $message): string
    {
        $result = $this->client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'user', 'content' => $message],
            ],
        ]);

        return $result->choices[0]->message->content;
    }
}
