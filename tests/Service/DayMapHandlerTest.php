<?php


namespace App\Tests\Service;


use App\Entity\Day;
use App\Entity\IDay;
use App\Service\DayMapHandler;
use App\Service\IDayMapper;
use App\Service\PositiveDayMapper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DayMapHandlerTest extends TestCase
{
    public function testMap()
    {
        /** @var IDay[] $days */
        $days = [
            $this->createMock(Day::class),
            $this->createMock(Day::class),
            $this->createMock(Day::class),
            $this->createMock(Day::class),
        ];

        /** @var IDayMapper | MockObject $mapper */
        $mapper = $this->createMock(PositiveDayMapper::class);
        $mapper->expects($this->exactly(count($days)))->method('map')->willReturn($this->createMock(Day::class));

        $handler = new DayMapHandler($mapper);
        self::assertEquals($days, $handler->map($days));
    }
}
