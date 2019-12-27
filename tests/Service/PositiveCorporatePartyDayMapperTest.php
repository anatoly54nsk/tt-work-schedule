<?php


namespace App\Tests\Service;


use App\Entity\Day;
use App\Entity\IDay;
use App\Entity\ITimeInterval;
use App\Entity\TimeInterval;
use App\Service\PositiveCorporatePartyDayMapper;
use App\Service\TimeIntervalFactory;
use DateTimeImmutable;
use Exception;
use Generator;
use PHPUnit\Framework\TestCase;

class PositiveCorporatePartyDayMapperTest extends TestCase
{
    /**
     * @dataProvider data
     * @param IDay $day
     * @param ITimeInterval[] $mapperIntervals
     * @param boolean $setDate
     * @param ITimeInterval[] $dayIntervals
     * @param DateTimeImmutable $dt
     * @param ITimeInterval[] $expected
     * @throws Exception
     */
    public function testMap($day, $mapperIntervals, $setDate, $dayIntervals, $dt, $expected)
    {
        $mapper = new PositiveCorporatePartyDayMapper($mapperIntervals, new TimeIntervalFactory());
        if ($setDate) {
            $mapper->setDate($dt);
        }
        $day->replaceTimeRanges($dayIntervals);
        self::assertEquals($expected, ($mapper->map($day))->getTimeRanges());
    }

    /**
     * @return Generator
     * @throws Exception
     */
    public function data()
    {
        $dt = (new DateTimeImmutable())->modify('midnight');
        yield [
            'day' => new Day($dt),
            'mapperIntervals' => [
                new TimeInterval(
                    $dt->modify('+12 hour')->format(ITimeInterval::FORMAT_TIME),
                    12,
                    ITimeInterval::UNITS_HOUR
                ),
            ],
            'setDate' => true,
            'dayIntervals' => [
                new TimeInterval(
                    $dt->format(ITimeInterval::FORMAT_TIME),
                    20,
                    ITimeInterval::UNITS_HOUR
                ),
            ],
            'dt' => $dt,
            'expected' => [
                new TimeInterval(
                    $dt->format(
                        ITimeInterval::FORMAT_TIME),
                    1440,
                    ITimeInterval::UNITS_MINUTE,
                    $dt
                ),
            ]
        ];
        yield [
            'day' => new Day($dt),
            'mapperIntervals' => [
                new TimeInterval(
                    $dt->modify('+12 hour')->format(ITimeInterval::FORMAT_TIME),
                    12,
                    ITimeInterval::UNITS_HOUR
                ),
            ],
            'setDate' => false,
            'dayIntervals' => [
                new TimeInterval(
                    $dt->format(ITimeInterval::FORMAT_TIME),
                    20,
                    ITimeInterval::UNITS_HOUR
                ),
            ],
            'dt' => $dt,
            'expected' => [
                new TimeInterval(
                    $dt->format(ITimeInterval::FORMAT_TIME),
                    1200,
                    ITimeInterval::UNITS_MINUTE,
                    $dt
                ),
            ]
        ];
        yield [
            'day' => new Day($dt),
            'mapperIntervals' => [
                new TimeInterval(
                    $dt->modify('+16 hour')->format(ITimeInterval::FORMAT_TIME),
                    8,
                    ITimeInterval::UNITS_HOUR
                ),
            ],
            'setDate' => false,
            'dayIntervals' => [
                new TimeInterval(
                    $dt->format(ITimeInterval::FORMAT_TIME),
                    8,
                    ITimeInterval::UNITS_HOUR
                ),
                new TimeInterval(
                    $dt->modify('+17 hour')->format(ITimeInterval::FORMAT_TIME),
                    7,
                    ITimeInterval::UNITS_HOUR
                ),
            ],
            'dt' => $dt,
            'expected' => [
                new TimeInterval(
                    $dt->format(ITimeInterval::FORMAT_TIME),
                    480,
                    ITimeInterval::UNITS_MINUTE,
                    $dt
                ),
                new TimeInterval(
                    $dt->modify('+17 hour')->format(ITimeInterval::FORMAT_TIME), 420, ITimeInterval::UNITS_MINUTE, $dt
                ),
            ]
        ];
        yield [
            'day' => new Day($dt),
            'mapperIntervals' => [
                new TimeInterval(
                    $dt->modify('+16 hour')->format(ITimeInterval::FORMAT_TIME),
                    8,
                    ITimeInterval::UNITS_HOUR
                ),
            ],
            'setDate' => true,
            'dayIntervals' => [
                new TimeInterval(
                    $dt->format(ITimeInterval::FORMAT_TIME),
                    8,
                    ITimeInterval::UNITS_HOUR
                ),
                new TimeInterval(
                    $dt->modify('+17 hour')->format(ITimeInterval::FORMAT_TIME),
                    7,
                    ITimeInterval::UNITS_HOUR
                ),
            ],
            'dt' => $dt,
            'expected' => [
                new TimeInterval(
                    $dt->format(ITimeInterval::FORMAT_TIME),
                    480,
                    ITimeInterval::UNITS_MINUTE,
                    $dt
                ),
                new TimeInterval(
                    $dt->modify('+16 hour')->format(ITimeInterval::FORMAT_TIME),
                    480,
                    ITimeInterval::UNITS_MINUTE,
                    $dt
                ),
            ]
        ];
        yield [
            'day' => new Day($dt),
            'mapperIntervals' => [
                new TimeInterval(
                    $dt->modify('+16 hour')->format(ITimeInterval::FORMAT_TIME),
                    8,
                    ITimeInterval::UNITS_HOUR
                ),
            ],
            'setDate' => true,
            'dayIntervals' => [
                new TimeInterval(
                    $dt->format(ITimeInterval::FORMAT_TIME),
                    8,
                    ITimeInterval::UNITS_HOUR
                ),
                new TimeInterval(
                    $dt->modify('+17 hour')->format(ITimeInterval::FORMAT_TIME),
                    7,
                    ITimeInterval::UNITS_HOUR
                ),
            ],
            'dt' => $dt->modify('+1 day'),
            'expected' => [
                new TimeInterval(
                    $dt->format(ITimeInterval::FORMAT_TIME),
                    480,
                    ITimeInterval::UNITS_MINUTE,
                    $dt
                ),
                new TimeInterval(
                    $dt->modify('+17 hour')->format(ITimeInterval::FORMAT_TIME),
                    420,
                    ITimeInterval::UNITS_MINUTE,
                    $dt
                ),
            ]
        ];
    }
}
