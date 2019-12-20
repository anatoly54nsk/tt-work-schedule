<?php


namespace App\Tests\Service;


use App\Entity\Day;
use App\Entity\IDay;
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
     * @throws Exception
     */
    public function testMapWithoutPrevious($dayIntervals, $mapperIntervals, $expected)
    {
        /** @var IDay | MockObject $day */
        $day = $this->createMock(Day::class);
        $day->expects($this->once())->method('getTimeRanges')->willReturn($dayIntervals);
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
                new TimeInterval($dt, $dt->modify('+3 hours')),
                new TimeInterval($dt->modify('+5 hours'), $dt->modify('+6 hours')),
                new TimeInterval($dt->modify('+8 hours'), $dt->modify('+12 hours')),
                new TimeInterval($dt->modify('+19 hours'), $dt->modify('+20 hours')),
            ],
            'mapperIntervals' => [
                new TimeInterval($dt->modify('+2 hours'), $dt->modify('+9 hours')),
                new TimeInterval($dt->modify('+13 hours'), $dt->modify('+16 hours')),
            ],
            'expected' => [
                new TimeInterval($dt, $dt->modify('+12 hours')),
                new TimeInterval($dt->modify('+13 hours'), $dt->modify('+16 hours')),
                new TimeInterval($dt->modify('+19 hours'), $dt->modify('+20 hours')),
            ],
        ];
        yield [
            'dayIntervals' => [
                new TimeInterval($dt->modify('+5 hours'), $dt->modify('+6 hours')),
                new TimeInterval($dt->modify('+13 hours'), $dt->modify('+16 hours')),
            ],
            'mapperIntervals' => [
                new TimeInterval($dt->modify('+13 hours'), $dt->modify('+16 hours')),
            ],
            'expected' => [
                new TimeInterval($dt->modify('+5 hours'), $dt->modify('+6 hours')),
                new TimeInterval($dt->modify('+13 hours'), $dt->modify('+16 hours')),
            ],
        ];
        yield [
            'dayIntervals' => [
                new TimeInterval($dt->modify('+3 hours'), $dt->modify('+6 hours')),
                new TimeInterval($dt->modify('+13 hours'), $dt->modify('+16 hours')),
            ],
            'mapperIntervals' => [
                new TimeInterval($dt->modify('+4 hours'), $dt->modify('+14 hours')),
            ],
            'expected' => [
                new TimeInterval($dt->modify('+3 hours'), $dt->modify('+16 hours')),
            ],
        ];
    }
}