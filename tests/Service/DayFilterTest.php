<?php


namespace App\Tests\Service;


use App\Entity\Day;
use App\Service\DayFilter;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class DayFilterTest extends TestCase
{
    public function testIsEmptyWithEmptyData()
    {
        $filter = new DayFilter();
        self::assertTrue($filter->isEmpty());
    }

    public function testIsEmptyWithData()
    {
        $filter = new DayFilter([new DateTimeImmutable()]);
        self::assertFalse($filter->isEmpty());
    }

    public function testIsDesired()
    {
        $dtSuccess = new DateTimeImmutable();
        $dtWrong = $dtSuccess->modify('-1 day');

        $filter = new DayFilter([$dtSuccess, $dtWrong]);
        $day = new Day($dtSuccess);

        self::assertTrue($filter->isDesired($day));
    }
}
