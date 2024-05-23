<?php

declare(strict_types=1);

namespace App\Services\OpenAI\Contracts;

interface OpenAIClient
{
    public function message(string $system, string $user): string;
}
