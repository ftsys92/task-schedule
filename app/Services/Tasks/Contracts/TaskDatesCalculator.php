<?php

declare(strict_types=1);

namespace App\Services\Tasks\Contracts;

use DateInterval;
use DatePeriod;
use DateTime;

interface TaskDatesCalculator
{
    /**
     * @param DateTime $fromDate
     * @param DateInterval $duration
     * @param list<array{start: string, end: string}> $timeline
     *
     * @return DatePeriod
     */
    public function calculateDates(DateTime $fromDate, DateInterval $duration, array $timeline): DatePeriod;
}
