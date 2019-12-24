<?php


namespace App\Tests\Service;


use App\Entity\ITimeInterval;
use App\Entity\TimeInterval;
use App\Service\TimeIntervalFactory;
use DateTimeImmutable;
use Exception;
use PHPUnit\Framework\TestCase;

class TimeIntervalFactoryTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testCreate()
    {
        $factory = new TimeIntervalFactory();
        $dt = (new DateTimeImmutable())->modify('midnight')->modify('+5 hour');
        $period = 12;
        $expected = new TimeInterval(
            $dt->format(ITimeInterval::FORMAT_TIME),
            $period * 60,
            ITimeInterval::UNITS_MINUTE,
            $dt->modify('midnight')
        );

        $actual = $factory->create($dt->getTimestamp(), $dt->modify("+{$period} hour")->getTimestamp());
        self::assertEquals($expected, $actual);
    }
}
