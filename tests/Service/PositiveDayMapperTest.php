<?php


namespace App\Tests\Service;


use App\Entity\Day;
use App\Entity\IDay;
use App\Entity\ITimeInterval;
use App\Entity\TimeInterval;
use App\Service\IDayMapper;
use App\Service\PositiveDayMapper;
use App\Service\TimeIntervalFactory;
use DateTimeImmutable;
use Exception;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PositiveDayMapperTest extends TestCase
{
    /**
     * @var TimeIntervalFactory
     */
    private $factory;

    public function setUp()
    {
        $this->factory = new TimeIntervalFactory();
    }

    /**
     * @throws Exception
     */
    public function testPrevious()
    {
        /** @var IDay | MockObject $day */
        $day = $this->createMock(Day::class);
        $day->expects($this->exactly(2))->method('getTimeRanges')->willReturnOnConsecutiveCalls([], []);
        $day->expects($this->exactly(1))->method('replaceTimeRanges')->with([]);

        /** @var IDayMapper | MockObject $previousMapper */
        $previousMapper = $this->createMock(PositiveDayMapper::class);
        $previousMapper->expects($this->once())->method('map')->with($day)->willReturn($day);

        $mapper = new PositiveDayMapper([], $this->factory, $previousMapper);
        $mapper->map($day);
    }

    /**
     * @dataProvider data
     * @param $dayIntervals
     * @param $recreatedIntervals
     * @param $mapperIntervals
     * @param $expected
     * @param $dt
     * @throws Exception
     */
    public function testMapWithoutPrevious($dayIntervals, $recreatedIntervals, $mapperIntervals, $expected, $dt)
    {
        /** @var IDay | MockObject $day */
        $day = $this
            ->getMockBuilder(Day::class)
            ->setConstructorArgs([$dt])
            ->setMethods(['getTimeRanges', 'replaceTimeRanges', 'getDt'])
            ->getMock();
        $day
            ->expects($this->exactly(2))
            ->method('getTimeRanges')
            ->willReturnOnConsecutiveCalls(
                $dayIntervals, $recreatedIntervals
            );
        $day->method('getDt')->willReturn($dt);
        $day
            ->expects($this->exactly(2))
            ->method('replaceTimeRanges')
            ->withConsecutive(
                [$this->equalTo($recreatedIntervals)],
                [$this->equalTo($expected)]
            );

        $mapper = new PositiveDayMapper($mapperIntervals, $this->factory);
        $expectedDay = $mapper->map($day);
        self::assertInstanceOf(IDay::class, $expectedDay);
    }

    /**
     * @return Generator
     * @throws Exception
     */
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
            'recreatedIntervals' => [
                new TimeInterval('00:00', 180, ITimeInterval::UNITS_MINUTE, $dt),
                new TimeInterval('05:00', 60, ITimeInterval::UNITS_MINUTE, $dt),
                new TimeInterval('08:00', 240, ITimeInterval::UNITS_MINUTE, $dt),
                new TimeInterval('19:00', 60, ITimeInterval::UNITS_MINUTE, $dt),
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
            'recreatedIntervals' => [
                new TimeInterval('00:00', 180, ITimeInterval::UNITS_MINUTE, $dt),
                new TimeInterval('05:00', 60, ITimeInterval::UNITS_MINUTE, $dt),
                new TimeInterval('08:00', 240, ITimeInterval::UNITS_MINUTE, $dt),
                new TimeInterval('19:00', 60, ITimeInterval::UNITS_MINUTE, $dt),
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
            'recreatedIntervals' => [
                new TimeInterval('05:00', 60, ITimeInterval::UNITS_MINUTE, $dt),
                new TimeInterval('13:00', 180, ITimeInterval::UNITS_MINUTE, $dt),
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
            'recreatedIntervals' => [
                new TimeInterval('05:00', 60, ITimeInterval::UNITS_MINUTE, $dt),
                new TimeInterval('13:00', 180, ITimeInterval::UNITS_MINUTE, $dt),
            ],
            'mapperIntervals' => [
                new TimeInterval('04:00', 10, ITimeInterval::UNITS_HOUR, $dt),
            ],
            'expected' => [
                new TimeInterval('04:00', 720, ITimeInterval::UNITS_MINUTE, $dt),
            ],
            'dt' => $dt,
        ];
        yield [
            'dayIntervals' => [
                new TimeInterval('05:00', 1, ITimeInterval::UNITS_HOUR),
                new TimeInterval('13:00', 3, ITimeInterval::UNITS_HOUR),
            ],
            'recreatedIntervals' => [
                new TimeInterval('05:00', 60, ITimeInterval::UNITS_MINUTE, $dt->modify('+1 day')),
                new TimeInterval('13:00', 180, ITimeInterval::UNITS_MINUTE, $dt->modify('+1 day')),
            ],
            'mapperIntervals' => [
                new TimeInterval('04:00', 10, ITimeInterval::UNITS_HOUR),
            ],
            'expected' => [
                new TimeInterval('04:00', 720, ITimeInterval::UNITS_MINUTE,
                    $dt->modify('+1 day')),
            ],
            'dt' => $dt->modify('+1 day'),
        ];
        yield [
            'dayIntervals' => [
                new TimeInterval('05:00', 1, ITimeInterval::UNITS_HOUR),
            ],
            'recreatedIntervals' => [
                new TimeInterval('05:00', 60, ITimeInterval::UNITS_MINUTE, $dt->modify('+1 day')),
            ],
            'mapperIntervals' => [
            ],
            'expected' => [
                new TimeInterval('05:00', 60, ITimeInterval::UNITS_MINUTE,
                    $dt->modify('+1 day')),
            ],
            'dt' => $dt->modify('+1 day'),
        ];
        yield [
            'dayIntervals' => [
                new TimeInterval('00:00', 8, ITimeInterval::UNITS_HOUR),
                new TimeInterval('17:00', 5, ITimeInterval::UNITS_HOUR),
            ],
            'recreatedIntervals' => [
                new TimeInterval('00:00', 480, ITimeInterval::UNITS_MINUTE, $dt),
                new TimeInterval('17:00', 300, ITimeInterval::UNITS_MINUTE, $dt),
            ],
            'mapperIntervals' => [
                new TimeInterval('12:00', 60, ITimeInterval::UNITS_MINUTE, $dt),
            ],
            'expected' => [
                new TimeInterval('00:00', 480, ITimeInterval::UNITS_MINUTE, $dt),
                new TimeInterval('12:00', 60, ITimeInterval::UNITS_MINUTE, $dt),
                new TimeInterval('17:00', 300, ITimeInterval::UNITS_MINUTE, $dt),
            ],
            'dt' => $dt,
        ];
    }
}
