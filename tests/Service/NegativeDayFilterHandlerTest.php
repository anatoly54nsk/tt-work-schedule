<?php


namespace App\Tests\Service;


use App\Entity\Day;
use App\Service\DayFilter;
use App\Service\NegativeDayFilterHandler;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class NegativeDayFilterHandlerTest extends TestCase
{
    private $handler;
    /**
     * @var DateTimeImmutable
     */
    private $dt;

    public function setUp()
    {
        $this->dt = new DateTimeImmutable();
        $filter = new DayFilter([$this->dt]);
        $this->handler = new NegativeDayFilterHandler($filter);
    }

    public function testFilter()
    {
        $dayNotFiltered = new Day($this->dt->modify('+1 day'));
        $dayFiltered = new Day($this->dt);
        $days = [
            $dayNotFiltered,
            $dayFiltered,
        ];

        $filteredDays = $this->handler->filter($days);

        self::assertCount(1, $filteredDays);
        self::assertEquals($filteredDays, [$dayNotFiltered]);
    }
}
