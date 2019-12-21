<?php


namespace App\Tests\Service;


use App\Entity\Day;
use App\Entity\IDay;
use App\Entity\ITimeInterval;
use App\Entity\TimeInterval;
use App\Service\IDayMapper;
use App\Service\PositiveDayMapper;
use DateTimeImmutable;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PositiveDayMapperTest extends TestCase
{
    public function testPrevious()
    {
        /** @var IDay | MockObject $day */
        $day = $this->createMock(Day::class);
        $day->expects($this->exactly(1))->method('getTimeRanges')->willReturn([]);
        $day->expects($this->never())->method('replaceTimeRanges');

        /** @var IDayMapper | MockObject $previousMapper */
        $previousMapper = $this->createMock(PositiveDayMapper::class);
        $previousMapper->expects($this->once())->method('map')->with($day)->willReturn($day);

        $mapper = new PositiveDayMapper([], $previousMapper);
        $mapper->map($day);
    }

    /**
     * @dataProvider data
     * @param $dayIntervals
     * @param $mapperIntervals
     * @param $expected
     * @param $dt
     * @throws Exception
     */
    public function testMapWithoutPrevious($dayIntervals, $mapperIntervals, $expected, $dt)
    {
        /** @var IDay | MockObject $day */
        $day = $this->createMock(Day::class);
        $day->expects($this->once())->method('getTimeRanges')->willReturn($dayIntervals);
        $day->method('__get')->with('dt')->willReturn($dt);
        $day->expects($this->once())->method('replaceTimeRanges')->with($expected);

        $mapper = new PositiveDayMapper($mapperIntervals);
        $expectedDay = $mapper->map($day);
        self::assertInstanceOf(IDay::class, $expectedDay);
    }

    public function data()
    {
        $dt = (new DateTimeImmutable())->modify('midnight');
        yield [
            'dayIntervals' => [
                new TimeInterval('00:00', 3, ITimeInterval::UNITS_HOUR, $dt),
                new TimeInterval('05:00', 1, ITimeInterval::UNITS_HOUR, $dt),
                new TimeInterval('08:00', 4, ITimeInterval::UNITS_HOUR, $dt),
                new TimeInterval('19:00', 1, ITimeInterval::UNITS_HOUR, $dt),
            ],
            'mapperIntervals' => [
                new TimeInterval('02:00', 7, ITimeInterval::UNITS_HOUR, $dt),
                new TimeInterval('13:00', 3, ITimeInterval::UNITS_HOUR, $dt),
            ],
            'expected' => [
                new TimeInterval('00:00', 720, ITimeInterval::UNITS_MINUTE, $dt),
                new TimeInterval('13:00', 180, ITimeInterval::UNITS_MINUTE, $dt),
                new TimeInterval('19:00', 60, ITimeInterval::UNITS_MINUTE, $dt),
            ],
            'dt' => $dt,
        ];
        yield [
            'dayIntervals' => [
                new TimeInterval('08:00', 4, ITimeInterval::UNITS_HOUR, $dt),
                new TimeInterval('19:00', 1, ITimeInterval::UNITS_HOUR, $dt),
                new TimeInterval('05:00', 1, ITimeInterval::UNITS_HOUR, $dt),
                new TimeInterval('00:00', 3, ITimeInterval::UNITS_HOUR, $dt),
            ],
            'mapperIntervals' => [
                new TimeInterval('13:00', 3, ITimeInterval::UNITS_HOUR, $dt),
                new TimeInterval('02:00', 7, ITimeInterval::UNITS_HOUR, $dt),
            ],
            'expected' => [
                new TimeInterval('00:00', 720, ITimeInterval::UNITS_MINUTE, $dt),
                new TimeInterval('13:00', 180, ITimeInterval::UNITS_MINUTE, $dt),
                new TimeInterval('19:00', 60, ITimeInterval::UNITS_MINUTE, $dt),
            ],
            'dt' => $dt,
        ];
        yield [
            'dayIntervals' => [
                new TimeInterval('05:00', 1, ITimeInterval::UNITS_HOUR, $dt),
                new TimeInterval('13:00', 3, ITimeInterval::UNITS_HOUR, $dt),
            ],
            'mapperIntervals' => [
                new TimeInterval('13:00', 3, ITimeInterval::UNITS_HOUR, $dt),
            ],
            'expected' => [
                new TimeInterval('05:00', 60, ITimeInterval::UNITS_MINUTE, $dt),
                new TimeInterval('13:00', 180, ITimeInterval::UNITS_MINUTE, $dt),
            ],
            'dt' => $dt,
        ];
        yield [
            'dayIntervals' => [
                new TimeInterval('05:00', 1, ITimeInterval::UNITS_HOUR, $dt),
                new TimeInterval('13:00', 3, ITimeInterval::UNITS_HOUR, $dt),
            ],
            'mapperIntervals' => [
                new TimeInterval('04:00', 10, ITimeInterval::UNITS_HOUR, $dt),
            ],
            'expected' => [
                new TimeInterval('04:00', 720, ITimeInterval::UNITS_MINUTE, $dt),
            ],
            'dt' => $dt,
        ];
    }
}
