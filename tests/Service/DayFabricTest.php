<?php


namespace App\Tests\Service;


use App\Entity\Day;
use App\Service\DayFabric;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class DayFabricTest extends TestCase
{
    /** @var DayFabric */
    private $fabric;

    public function setUp()
    {
        $this->fabric = new DayFabric();
    }

    public function testCreate()
    {
        $dt = new DateTimeImmutable();
        $day = $this->fabric->create($dt);

        self::assertInstanceOf(Day::class, $day);
    }
}
