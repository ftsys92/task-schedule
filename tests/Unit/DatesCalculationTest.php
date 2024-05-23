<?php

declare(strict_types=1);

namespace Tests\Unit;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class DatesCalculationTest extends TestCase
{

    public static function provides8HoursWorkAnd1HourLunchTestSet(): array
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

        return [
            [
                [
                    'date' => '2024-05-22 17:45',
                    'duration' => 'PT5H25M',
                    'timeline' => $timeline,
                    'expected_start_date' => '2024-05-22 17:45',
                    'expected_end_date' => '2024-05-23 15:10',
                ],
                [
                    'date' => '2024-05-22 17:50',
                    'duration' => 'PT5H25M',
                    'timeline' => $timeline,
                    'expected_start_date' => '2024-05-23 09:00',
                    'expected_end_date' => '2024-05-23 15:25',
                ],
                [
                    'date' => '2024-05-23 09:25',
                    'duration' => 'PT5H25M',
                    'timeline' => $timeline,
                    'expected_start_date' => '2024-05-23 09:25',
                    'expected_end_date' => '2024-05-23 15:50',
                ],
                [
                    'date' => '2024-05-23 12:25',
                    'duration' => 'PT10H',
                    'timeline' => $timeline,
                    'expected_start_date' => '2024-05-23 13:00',
                    'expected_end_date' => '2024-05-24 15:00',
                ],
                [
                    'date' => '2024-05-23 13:20',
                    'duration' => 'PT10H',
                    'timeline' => $timeline,
                    'expected_start_date' => '2024-05-23 13:20',
                    'expected_end_date' => '2024-05-23 15:20',
                ],
            ],
        ];
    }

    #[DataProvider('provides8HoursWorkAnd1HourLunchTestSet')]
    public function test_calculates_start_date_and_end_date_based_on_duration_and_timeline(array $testSet): void
    {
        $timeline = $testSet['timeline'];

        $duration = new CarbonInterval($testSet['duration']);
        $startDate = Carbon::parse($testSet['date']);

        $adjustedStartDate = $this->calculateStartDate($startDate, $timeline);
        $endDate = $this->calculateEndDate($adjustedStartDate, $duration, $timeline);

        self::assertEquals($testSet['expected_start_date'], $adjustedStartDate->format('Y-m-d H:i'));
        self::assertEquals($testSet['expected_end_date'], $endDate->format('Y-m-d H:i'));
    }

    /**
     * Adjusts the start date to fit within the working periods.
     *
     * @param Carbon $startDate
     * @param array $timeline
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
     * @param array $timeline
     *
     * @return Carbon
     */
    private function calculateEndDate(Carbon $startDate, CarbonInterval $duration, array $timeline): Carbon
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

            // Move to the next day's first period if there's remaining duration.
            $date->addDay()->startOfDay();
            $endDate = Carbon::createFromTimeString($timeline[0]['start'])->setDate($date->year, $date->month, $date->day);
        }

        return $endDate;
    }
}
