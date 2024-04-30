<?php

namespace App\Services\OpenAI\Contracts;

interface OpenAIClient
{
    public function message(string $system, string $user): string;
}
