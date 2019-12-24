<?php


namespace App\Tests\Service;


use App\Entity\Day;
use App\Entity\IDay;
use App\Service\IDayMapper;
use App\Service\PositiveDayMapper;
use App\Service\TimeIntervalFactory;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DayMapperTest extends TestCase
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
    public function testSetPrevious()
    {
        /** @var IDay | MockObject $day */
        $day = $this->createMock(Day::class);
        $day->method('getTimeRanges')->willReturn([]);
        $day->method('replaceTimeRanges');

        /** @var IDayMapper | MockObject $previousMapper */
        $previousMapper = $this->createMock(PositiveDayMapper::class);
        $previousMapper->expects($this->once())->method('map')->with($day)->willReturn($day);

        $mapper = new PositiveDayMapper([], $this->factory);
        $mapper->setPrevious($previousMapper);
        $mapper->map($day);
    }
}
