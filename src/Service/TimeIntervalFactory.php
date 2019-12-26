<?php


namespace App\Service;


use App\Entity\ITimeInterval;
use App\Entity\TimeInterval;
use DateTimeImmutable;
use Exception;

class TimeIntervalFactory implements ITimeIntervalFactory
{
    /**
     * @param int $startTimestamp
     * @param int $endTimestamp
     * @return ITimeInterval
     * @throws Exception
     */
    public function createFormTimestamp(int $startTimestamp, int $endTimestamp): ITimeInterval
    {
        $dt = new DateTimeImmutable();
        $start = $dt->setTimestamp($startTimestamp);
        $minutes = ($endTimestamp - $startTimestamp) / 60;
        return new TimeInterval(
            $start->format(ITimeInterval::FORMAT_TIME),
            $minutes,
            ITimeInterval::UNITS_MINUTE,
            $start->modify('midnight')
        );
    }

    /**
     * @param string $startTime
     * @param int $period
     * @param string $units
     * @param DateTimeImmutable|null $dt
     * @return ITimeInterval
     * @throws Exception
     */
    public function create(string $startTime, int $period, string $units = ITimeInterval::UNITS_MINUTE, DateTimeImmutable $dt = null): ITimeInterval
    {
        return new TimeInterval(
            $startTime,
            $period,
            $units,
            $dt
        );
    }
}
