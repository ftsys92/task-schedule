<?php

declare(strict_types=1);

namespace App\Services\Tasks;

use App\Services\Tasks\Contracts\TaskDatesCalculator;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use DateTime;
use DateInterval;
use DatePeriod;
use InvalidArgumentException;

final class CarbonTaskDatesCalculator implements TaskDatesCalculator
{
    public function calculateDates(DateTime $fromDate, DateInterval $duration, array $timeline): DatePeriod
    {
        if (!$this->validateTimeline($timeline)) {
            throw new InvalidArgumentException(
                sprintf('%s(): Argument #3 ($timeline) is not valid', __METHOD__),
            );
        }

        $start = $this->calculateStartDate(new Carbon($fromDate), $timeline);
        $end = $this->calculateEndDate($start, new CarbonInterval($duration), $timeline);

        return CarbonPeriod::create($start, $end);
    }

    /**
     * @param list<array{start: non-empty-string, end: non-empty-string}> $timeline
     */
    private function validateTimeline(array $timeline): bool
    {
        if ([] === $timeline) {
            return false;
        }

        foreach ($timeline as $time) {
            if (!is_array($time) || !isset($time['start']) || !isset($time['end'])) {
                return false;
            }

            return preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $time['start']) === 1 &&
                preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $time['end']) === 1;
        }

        return true;
    }

    /**
     * Adjusts the start date to fit within the working periods.
     *
     * @param Carbon $startDate
     * @param list<array{start: non-empty-string, end: non-empty-string}> $timeline
     *
     * @return Carbon
     */
    private function calculateStartDate(Carbon $startDate, array $timeline): Carbon
    {
        $date = $startDate->copy();

        foreach ($timeline as $time) {
            $start = $this->adjustWeekends(
                Carbon::createFromTimeString($time['start'])->setDate($date->year, $date->month, $date->day)
            );
            $end = $this->adjustWeekends(
                Carbon::createFromTimeString($time['end'])->setDate($date->year, $date->month, $date->day)
            );

            if ($date->between($start, $end, true) && $date->diffInMinutes($end) >= 15) {
                return $this->adjustWeekends($date); // Start date is within this period.
            } elseif ($date->lessThan($start)) {
                return $start; // Adjust start date to the beginning of this period.
            }
        }

        // If the start date is after all working periods, move to the next day's first period.
        $nextDay = $date->addDay()->startOfDay();
        $startDate = Carbon::createFromTimeString($timeline[0]['start'])->setDate($nextDay->year, $nextDay->month, $nextDay->day);

        return $this->adjustWeekends($startDate);
    }

    /**
     * Calculates the end date based on the start date, duration, and working periods.
     *
     * @param Carbon $startDate
     * @param CarbonInterval $duration
     * @param list<array{start: non-empty-string, end: non-empty-string}> $timeline
     *
     * @return Carbon
     */
    private function calculateEndDate(Carbon $startDate, CarbonInterval $duration, array $timeline): Carbon
    {
        $remainingDuration = $duration->copy();
        $date = $startDate->copy();
        $endDate = $startDate->copy();

        while ($remainingDuration->totalMinutes > 0) {
            foreach ($timeline as $time) {
                $start = $this->adjustWeekends(
                    Carbon::createFromTimeString($time['start'])->setDate($date->year, $date->month, $date->day)
                );
                $end = $this->adjustWeekends(
                    Carbon::createFromTimeString($time['end'])->setDate($date->year, $date->month, $date->day)
                );

                if ($endDate->lessThan($start)) {
                    $endDate = $start;
                }

                if ($endDate->between($start, $end, true)) {
                    $periodEnd = $end->copy();
                    $periodDuration = $endDate->diffAsCarbonInterval($periodEnd);

                    if ($remainingDuration->totalMinutes <= $periodDuration->totalMinutes) {
                        $endDate->add($remainingDuration);

                        return $this->adjustWeekends($endDate);
                    } else {
                        $endDate->add($periodDuration);
                        $remainingDuration->subtract($periodDuration);
                    }
                }
            }

            // Move to the next day's first period if there's remaining duration.
            $date->addDay()->startOfDay();
            $endDate = Carbon::createFromTimeString($timeline[0]['start'])->setDate($date->year, $date->month, $date->day);

            $endDate = $this->adjustWeekends($endDate);
        }

        return $endDate;
    }

    private function adjustWeekends(Carbon $date): Carbon
    {
        $adjustedDate = $date->copy();
        while ($adjustedDate->isWeekend()) {
            $adjustedDate->addDay();
        }

        return $adjustedDate;
    }
}
