<?php


namespace App\Tests\Service;


use App\Entity\Day;
use App\Entity\IDay;
use App\Service\DayFactory;
use App\Service\IDayFactory;
use DateTimeImmutable;
use Exception;
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

    /**
     * @dataProvider data
     * @param $dateBegin
     * @param $dateEnd
     * @param $mode
     * @param $expected
     */
    public function testCreateDaysByRange($dateBegin, $dateEnd, $mode, $expected)
    {
        $days = $this->factory->createDaysByRange($dateBegin, $dateEnd, $mode);

        self::assertEquals($expected, $days);
    }

    public function testWrongMode()
    {
        $dt = new DateTimeImmutable();
        $mode = 'mode';

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Undefined mode.');
        $this->factory->create($dt, $mode);
    }

    /**
     * @dataProvider datesData
     * @param $dates
     * @param $mode
     * @param $expected
     */
    public function testCreateFromDatesArray($dates, $mode, $expected)
    {
        $days = $this->factory->createFromDatesArray($dates, $mode);

        self::assertEquals($expected, $days);
    }

    public function data()
    {
        yield [
            'dateBegin' => DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-23')->modify('midnight'),
            'dateEnd' => DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-29')->modify('midnight'),
            'mode' => IDayFactory::MODE_ALL,
            'expected' => [
                new Day(DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-23')->modify('midnight')),
                new Day(DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-24')->modify('midnight')),
                new Day(DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-25')->modify('midnight')),
                new Day(DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-26')->modify('midnight')),
                new Day(DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-27')->modify('midnight')),
                new Day(DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-28')->modify('midnight')),
                new Day(DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-29')->modify('midnight')),
            ],
        ];
        yield [
            'dateBegin' => DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-23')->modify('midnight'),
            'dateEnd' => DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-29')->modify('midnight'),
            'mode' => IDayFactory::MODE_WEEKENDS,
            'expected' => [
                new Day(DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-28')->modify('midnight')),
                new Day(DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-29')->modify('midnight')),
            ],
        ];
        yield [
            'dateBegin' => DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-23')->modify('midnight'),
            'dateEnd' => DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-29')->modify('midnight'),
            'mode' => IDayFactory::MODE_WITHOUT_WEEKENDS,
            'expected' => [
                new Day(DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-23')->modify('midnight')),
                new Day(DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-24')->modify('midnight')),
                new Day(DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-25')->modify('midnight')),
                new Day(DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-26')->modify('midnight')),
                new Day(DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-27')->modify('midnight')),
            ],
        ];
    }

    public function datesData()
    {
        $dates = [
            DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-23')->modify('midnight'),
            DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-24')->modify('midnight'),
            DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-25')->modify('midnight'),
            DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-26')->modify('midnight'),
            DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-27')->modify('midnight'),
            DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-28')->modify('midnight'),
            DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-29')->modify('midnight'),
        ];
        yield [
            'dates' => $dates,
            'mode' => IDayFactory::MODE_ALL,
            'expected' => [
                new Day(DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-23')->modify('midnight')),
                new Day(DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-24')->modify('midnight')),
                new Day(DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-25')->modify('midnight')),
                new Day(DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-26')->modify('midnight')),
                new Day(DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-27')->modify('midnight')),
                new Day(DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-28')->modify('midnight')),
                new Day(DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-29')->modify('midnight')),
            ],
        ];
        yield [
            'dates' => $dates,
            'mode' => IDayFactory::MODE_WEEKENDS,
            'expected' => [
                new Day(DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-28')->modify('midnight')),
                new Day(DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-29')->modify('midnight')),
            ],
        ];
        yield [
            'dates' => $dates,
            'mode' => IDayFactory::MODE_WITHOUT_WEEKENDS,
            'expected' => [
                new Day(DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-23')->modify('midnight')),
                new Day(DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-24')->modify('midnight')),
                new Day(DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-25')->modify('midnight')),
                new Day(DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-26')->modify('midnight')),
                new Day(DateTimeImmutable::createFromFormat(IDay::DAY_FORMAT, '2019-12-27')->modify('midnight')),
            ],
        ];
    }
}
