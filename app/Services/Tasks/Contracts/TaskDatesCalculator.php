<?php

declare(strict_types=1);

namespace App\Services\Tasks\Contracts;

use DateInterval;
use DatePeriod;
use DateTime;
use InvalidArgumentException;

interface TaskDatesCalculator
{
    /**
     * @param DateTime $fromDate
     * @param DateInterval $duration
     * @param list<array{start: non-empty-string, end: non-empty-string}> $timeline
     *
     * @throws InvalidArgumentException
     *
     * @return DatePeriod
     */
    public function calculateDates(DateTime $fromDate, DateInterval $duration, array $timeline): DatePeriod;
}
