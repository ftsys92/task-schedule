<?php

declare(strict_types=1);

namespace App\Services\Tasks\Contracts;

interface TaskEstimator
{
    /**
     * @param non-empty-string $title
     * @param non-empty-string|null $notes
     */
    public function estimate(string $title, ?string $notes): ?string;
}
