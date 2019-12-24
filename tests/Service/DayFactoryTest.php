<?php


namespace App\Tests\Service;


use App\Entity\Day;
use App\Service\DayFactory;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class DayFactoryTest extends TestCase
{
    /** @var DayFactory */
    private $factory;

    public function setUp()
    {
        $this->factory = new DayFactory();
    }

    public function testCreate()
    {
        $dt = new DateTimeImmutable();
        $day = $this->factory->create($dt);

        self::assertInstanceOf(Day::class, $day);
    }
}
