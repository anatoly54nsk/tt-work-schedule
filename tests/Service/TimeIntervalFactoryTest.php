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
     * @var TimeIntervalFactory
     */
    private $factory;
    /**
     * @var DateTimeImmutable
     */
    private $dt;
    /**
     * @var TimeInterval
     */
    private $expected;
    private $period;

    /**
     * @throws Exception
     */
    public function setUp()
    {
        $this->factory = new TimeIntervalFactory();
        $this->dt = (new DateTimeImmutable())->modify('midnight')->modify('+5 hour');
        $this->period = 12;
        $this->expected = new TimeInterval(
            $this->dt->format(ITimeInterval::FORMAT_TIME),
            $this->period * 60,
            ITimeInterval::UNITS_MINUTE,
            $this->dt->modify('midnight')
        );
    }

    /**
     * @throws Exception
     */
    public function testCreateFormTimestamp()
    {
        $actual = $this->factory->createFormTimestamp(
            $this->dt->getTimestamp(),
            $this->dt->modify("+{$this->period} hour")->getTimestamp()
        );
        self::assertEquals($this->expected, $actual);
    }

    /**
     * @throws Exception
     */
    public function testCreate()
    {
        $actual = $this->factory->create(
            $this->dt->format(ITimeInterval::FORMAT_TIME),
            $this->period * 60,
            ITimeInterval::UNITS_MINUTE,
            $this->dt->modify('midnight')
        );
        self::assertEquals($this->expected, $actual);
    }
}
