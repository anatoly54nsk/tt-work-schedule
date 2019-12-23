<?php


namespace App\Tests\Entity;


use App\Entity\Day;
use App\Entity\IDay;
use App\Entity\ITimeInterval;
use App\Entity\TimeInterval;
use DateTimeImmutable;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DayTest extends TestCase
{
    /**
     * @var DateTimeImmutable
     */
    private $dt;
    /**
     * @var Day
     */
    private $day;

    public function setUp()
    {
        $this->dt = new DateTimeImmutable();
        $this->day = new Day($this->dt);
    }

    public function testCreate()
    {
        self::assertEquals($this->dt->format(IDay::DAY_FORMAT), $this->day->day);
    }

    public function testGetTimestamp()
    {
        self::assertEquals($this->dt->modify('midnight')->getTimestamp(), $this->day->getDayBeginTimestamp());
    }

    public function testWrongParamReadException()
    {
        self::expectException(Exception::class);
        $this->day->wrongParam;
    }

    public function testGetTimeRanges()
    {
        self::assertEquals([], $this->day->getTimeRanges());
    }

    /**
     * @throws Exception
     */
    public function testReplaceTimeRanges()
    {
        self::assertEquals([], $this->day->getTimeRanges());
        $ranges = [
            new TimeInterval($this->dt->format(ITimeInterval::FORMAT_TIME), 2, ITimeInterval::UNITS_HOUR)
        ];
        $this->day->replaceTimeRanges($ranges);

        self::assertEquals($ranges, $this->day->getTimeRanges());
    }

    public function testJsonSerialise()
    {
        $json = json_encode(
            [
                'day' => $this->dt->format(IDay::DAY_FORMAT),
                'timeRanges' => []
            ]
        );

        self::assertEquals($json, json_encode($this->day));
    }

    public function testGetDt()
    {
        /** @var DateTimeImmutable | MockObject $dt */
        $dt = $this->createMock(DateTimeImmutable::class);
        $dt->expects($this->once())->method('format')->with(ITimeInterval::FORMAT_FULL);

        $day = new Day($dt);
        $expectedDt = $day->dt;
        $expectedDt->format(ITimeInterval::FORMAT_FULL);
    }
}
