<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Tasks\TaskDatesCalculator;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class TaskDatesCalculatorTest extends TestCase
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
            ],
            [
                [
                    'date' => '2024-05-22 17:50',
                    'duration' => 'PT5H25M',
                    'timeline' => $timeline,
                    'expected_start_date' => '2024-05-23 09:00',
                    'expected_end_date' => '2024-05-23 15:25',
                ],
            ],
            [
                [
                    'date' => '2024-05-23 09:25',
                    'duration' => 'PT5H25M',
                    'timeline' => $timeline,
                    'expected_start_date' => '2024-05-23 09:25',
                    'expected_end_date' => '2024-05-23 15:50',
                ],
            ],
            [
                [
                    'date' => '2024-05-23 12:25',
                    'duration' => 'PT10H',
                    'timeline' => $timeline,
                    'expected_start_date' => '2024-05-23 13:00',
                    'expected_end_date' => '2024-05-24 15:00',
                ],
            ],
            [
                [
                    'date' => '2024-05-23 13:20',
                    'duration' => 'PT10H',
                    'timeline' => $timeline,
                    'expected_start_date' => '2024-05-23 13:20',
                    'expected_end_date' => '2024-05-24 15:20',
                ],
            ],
            [
                [
                    'date' => '2024-05-24 17:00',
                    'duration' => 'PT2H',
                    'timeline' => $timeline,
                    'expected_start_date' => '2024-05-24 17:00',
                    'expected_end_date' => '2024-05-27 10:00',
                ],
            ],
            [
                [
                    'date' => '2024-05-25 09:00',
                    'duration' => 'PT4H',
                    'timeline' => $timeline,
                    'expected_start_date' => '2024-05-27 09:00',
                    'expected_end_date' => '2024-05-27 14:00',
                ],
            ],
            [
                [
                    'date' => '2024-05-25 18:00',
                    'duration' => 'PT4H',
                    'timeline' => $timeline,
                    'expected_start_date' => '2024-05-27 09:00',
                    'expected_end_date' => '2024-05-27 14:00',
                ],
            ],
            [
                [
                    'date' => '2024-05-25 17:45',
                    'duration' => 'PT4H',
                    'timeline' => $timeline,
                    'expected_start_date' => '2024-05-27 09:00',
                    'expected_end_date' => '2024-05-27 14:00',
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

        $calculator = new TaskDatesCalculator();
        $dates = $calculator->calculateDates($startDate, $duration, $timeline);

        self::assertEquals($testSet['expected_start_date'], $dates->getStartDate()->format('Y-m-d H:i'));
        self::assertEquals($testSet['expected_end_date'], $dates->getEndDate()->format('Y-m-d H:i'));
    }
}
