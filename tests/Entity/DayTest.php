<?php


namespace App\Tests\Entity;


use App\Entity\Day;
use DateTimeImmutable;
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
        self::assertEquals($this->dt->format('Y-m-d'), $this->day->day);
    }

    public function testGetTimestamp()
    {
        self::assertEquals($this->dt->modify('midnight')->getTimestamp(),  $this->day->getDayBeginTimestamp());
    }
}
