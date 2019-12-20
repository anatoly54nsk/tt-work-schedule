<?php


namespace App\Tests\Entity;


use App\Entity\ITimeInterval;
use App\Entity\TimeInterval;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class TimeIntervalTest extends TestCase
{
    /**
     * @var DateTimeImmutable
     */
    private $begin;
    /**
     * @var DateTimeImmutable
     */
    private $end;
    /**
     * @var TimeInterval
     */
    private $timeInterval;

    public function setUp()
    {
        $this->begin = new DateTimeImmutable();
        $this->end = $this->begin->modify('+1 hour');
        $this->timeInterval = new TimeInterval($this->begin, $this->end);
    }

    public function testGetBegin()
    {
        self::assertEquals($this->begin->getTimestamp(), $this->timeInterval->getStart());
    }

    public function testGetEnd()
    {
        self::assertEquals($this->end->getTimestamp(), $this->timeInterval->getEnd());
    }

    public function testJsonSerialise()
    {
        $json = json_encode(
            [
                'start' => $this->begin->format(ITimeInterval::TIME_FORMAT),
                'end' => $this->end->format(ITimeInterval::TIME_FORMAT)
            ]
        );

        self::assertEquals($json, json_encode($this->timeInterval));
    }
}
