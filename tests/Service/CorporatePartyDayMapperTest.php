<?php


namespace App\Tests\Service;


use App\Entity\Day;
use App\Entity\IDay;
use App\Entity\ITimeInterval;
use App\Entity\TimeInterval;
use App\Service\CorporatePartyDayMapper;
use App\Service\TimeIntervalFactory;
use DateTimeImmutable;
use Exception;
use PHPUnit\Framework\TestCase;

class CorporatePartyDayMapperTest extends TestCase
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
        $mapper = new CorporatePartyDayMapper($mapperIntervals, new TimeIntervalFactory());
        if ($setDate) {
            $mapper->setDate($dt);
        }
        $day->replaceTimeRanges($dayIntervals);
        self::assertEquals($expected, ($mapper->map($day))->getTimeRanges());
    }

    public function data()
    {
        $dt = (new DateTimeImmutable())->modify('midnight');
        yield [
            'day' => new Day($dt),
            'mapperIntervals' => [
                new TimeInterval($dt->modify('+12 hour')->format(ITimeInterval::FORMAT_TIME), 12, ITimeInterval::UNITS_HOUR),
            ],
            'setDate' => true,
            'dayIntervals' => [
                new TimeInterval($dt->format(ITimeInterval::FORMAT_TIME), 20, ITimeInterval::UNITS_HOUR),
            ],
            'dt' => $dt,
            'expected' => [
                new TimeInterval($dt->format(ITimeInterval::FORMAT_TIME), 720, ITimeInterval::UNITS_MINUTE, $dt),
            ]
        ];
        yield [
            'day' => new Day($dt),
            'mapperIntervals' => [
                new TimeInterval($dt->modify('+12 hour')->format(ITimeInterval::FORMAT_TIME), 12, ITimeInterval::UNITS_HOUR),
            ],
            'setDate' => false,
            'dayIntervals' => [
                new TimeInterval($dt->format(ITimeInterval::FORMAT_TIME), 20, ITimeInterval::UNITS_HOUR),
            ],
            'dt' => $dt,
            'expected' => [
                new TimeInterval($dt->format(ITimeInterval::FORMAT_TIME), 1200, ITimeInterval::UNITS_MINUTE, $dt),
            ]
        ];
    }
}
