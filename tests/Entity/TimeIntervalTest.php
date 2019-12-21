<?php


namespace App\Tests\Entity;


use App\Entity\ITimeInterval;
use App\Entity\TimeInterval;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TimeIntervalTest extends TestCase
{
    /**
     * @var DateTimeImmutable
     */
    private $begin;
    /**
     * @var TimeInterval
     */
    private $timeInterval;
    /**
     * @var int
     */
    private $period;
    /**
     * @var string
     */
    private $units;
    /**
     * @var DateTimeImmutable|false
     */
    private $startDate;

    public function setUp()
    {
        $this->begin = new DateTimeImmutable();
        $this->period = 2;
        $this->units = ITimeInterval::UNITS_HOUR;
        $startTime = $this->begin->format(ITimeInterval::FORMAT_TIME);
        $this->timeInterval = new TimeInterval(
            $startTime,
            $this->period,
            $this->units,
            $this->begin
        );
        $date = $this->begin->format(ITimeInterval::FORMAT_DATE);
        $this->startDate = DateTimeImmutable::createFromFormat(
            ITimeInterval::FORMAT_FULL, "{$date} {$startTime}");
    }

    public function testGetBegin()
    {
        self::assertEquals($this->startDate->getTimestamp(), $this->timeInterval->getStart());
    }

    public function testGetEnd()
    {
        self::assertEquals($this->startDate->modify("+{$this->period} {$this->units}")->getTimestamp(), $this->timeInterval->getEnd());
    }

    public function testJsonSerialise()
    {
        $json = json_encode(
            [
                'start' => $this->startDate->format(ITimeInterval::FORMAT_TIME),
                'end' => $this->startDate->modify("+{$this->period} {$this->units}")->format(ITimeInterval::FORMAT_TIME)
            ]
        );

        self::assertEquals($json, json_encode($this->timeInterval));
    }

    public function testSetDate()
    {
        $date = $this->begin->format(ITimeInterval::FORMAT_DATE);
        $startTime = $this->begin->format(ITimeInterval::FORMAT_TIME);
        $timestamp = (DateTimeImmutable::createFromFormat(ITimeInterval::FORMAT_FULL, "{$date} {$startTime}"))->getTimestamp();

        /** @var DateTimeImmutable | MockObject $dt */
        $dt = $this->createMock(DateTimeImmutable::class);
        $dt->expects($this->exactly(2))->method('format')->willReturn($date);
        $dt->expects($this->exactly(2))->method('setTimestamp')->with($timestamp)->willReturn($dt);
        $dt->expects($this->once())->method('modify')->with("+{$this->period} {$this->units}")->willReturn($dt);
        $dt->expects($this->exactly(2))->method('getTimestamp')->willReturn($timestamp);

        $this->timeInterval->setDate($dt);

        self::assertEquals($timestamp, $this->timeInterval->getStart());
        self::assertEquals($timestamp, $this->timeInterval->getEnd());
    }
}
