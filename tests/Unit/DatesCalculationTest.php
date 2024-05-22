<?php

namespace Tests\Feature\Jobs;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use PHPUnit\Framework\TestCase;

class DatesCalculationTest extends TestCase
{
    public function test_calculates_start_date_and_end_date_for_a_task(): void
    {
        $timeline = [
            [
                'start' => '09:00',
                'end' => '12:00',
            ],
            [
                'start' => '13:00',
                'end' => '18:00',
            ],
        ];

        $duration = new CarbonInterval('PT10H');
        $startDate = Carbon::parse('2024-05-23 12:25');

        $adjustedStartDate = $this->adjustStartDate($startDate, $timeline);
        $endDate = $this->calculateEndDate($adjustedStartDate, $duration, $timeline);

        self::assertEquals('2024-05-23 13:00', $adjustedStartDate->format('Y-m-d H:i'));
        self::assertEquals('2024-05-24 15:00', $endDate->format('Y-m-d H:i'));
    }

    /**
     * Adjusts the start date to fit within the working periods
     *
     * @param Carbon $startDate
     * @param array $timeline
     * @return Carbon
     */
    private function adjustStartDate(Carbon $startDate, $timeline): Carbon
    {
        $date = $startDate->copy();
        foreach ($timeline as $time) {
            $start = Carbon::createFromTimeString($time['start'])->setDate($date->year, $date->month, $date->day);
            $end = Carbon::createFromTimeString($time['end'])->setDate($date->year, $date->month, $date->day);

            if ($date->between($start, $end, true)) {
                return $date; // Start date is within this period
            } elseif ($date->lessThan($start)) {
                return $start; // Adjust start date to the beginning of this period
            }
        }

        // If the start date is after all working periods, move to the next day's first period
        $nextDay = $date->addDay()->startOfDay();
        $firstPeriod = $timeline[0];
        return Carbon::createFromTimeString($firstPeriod['start'])->setDate($nextDay->year, $nextDay->month, $nextDay->day);
    }

    /**
     * Calculates the end date based on the start date, duration, and working periods
     *
     * @param Carbon $startDate
     * @param CarbonInterval $duration
     * @param array $timeline
     * @return Carbon
     */
    private function calculateEndDate(Carbon $startDate, CarbonInterval $duration, $timeline): Carbon
    {
        $remainingDuration = $duration->copy();
        $date = $startDate->clone();
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

            // Move to the next day's first period if there's remaining duration
            $date->addDay()->startOfDay();
            $endDate = Carbon::createFromTimeString($timeline[0]['start'])->setDate($date->year, $date->month, $date->day);
        }

        return $endDate;
    }
}
