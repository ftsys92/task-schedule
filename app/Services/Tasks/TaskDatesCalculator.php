<?php

namespace App\Services\Tasks;

use App\Services\Tasks\Contracts\TaskDatesCalculator as TaskDatesCalculatorContract;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use DateTime;
use DateInterval;
use DatePeriod;

final class TaskDatesCalculator implements TaskDatesCalculatorContract
{
    public function calculateDates(DateTime $fromDate, DateInterval $duration, array $timeline): DatePeriod
    {
        $start = $this->calculateStartDate($fromDate, $timeline);
        $end = $this->calculateEndDate($start, $duration, $timeline);

        return CarbonPeriod::create($start, $end);
    }

    /**
     * Adjusts the start date to fit within the working periods.
     *
     * @param Carbon $startDate
     * @param list<array{start: string, end: string}> $timeline
     *
     * @return Carbon
     */
    private function calculateStartDate(Carbon $startDate, array $timeline): Carbon
    {
        $date = $startDate->copy();
        foreach ($timeline as $time) {
            $start = Carbon::createFromTimeString($time['start'])->setDate($date->year, $date->month, $date->day);
            $end = Carbon::createFromTimeString($time['end'])->setDate($date->year, $date->month, $date->day);

            if ($date->between($start, $end, false) && $date->diffInMinutes($end) >= 15) {
                return $date; // Start date is within this period.
            } elseif ($date->lessThan($start)) {
                return $start; // Adjust start date to the beginning of this period.
            }
        }

        // If the start date is after all working periods, move to the next day's first period.
        $nextDay = $date->addDay()->startOfDay();
        $firstPeriod = $timeline[0];

        return Carbon::createFromTimeString($firstPeriod['start'])->setDate($nextDay->year, $nextDay->month, $nextDay->day);
    }

    /**
     * Calculates the end date based on the start date, duration, and working periods.
     *
     * @param Carbon $startDate
     * @param CarbonInterval $duration
     * @param list<array{start: string, end: string}> $timeline
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
                $start = Carbon::createFromTimeString($time['start'])->setDate($date->year, $date->month, $date->day);
                $end = Carbon::createFromTimeString($time['end'])->setDate($date->year, $date->month, $date->day);

                if ($endDate->lessThan($start)) {
                    $endDate = $start;
                }

                if ($endDate->between($start, $end, true)) {
                    $periodEnd = $end->copy();
                    $periodDuration = $endDate->diffAsCarbonInterval($periodEnd);

                    if ($remainingDuration->totalMinutes <= $periodDuration->totalMinutes) {
                        $endDate->add($remainingDuration);
                        return $endDate;
                    } else {
                        $endDate->add($periodDuration);
                        $remainingDuration->subtract($periodDuration);
                    }
                }
            }

            // Move to the next day's first period if there's remaining duration.
            $date->addDay()->startOfDay();
            $endDate = Carbon::createFromTimeString($timeline[0]['start'])->setDate($date->year, $date->month, $date->day);
        }

        return $endDate;
    }
}
