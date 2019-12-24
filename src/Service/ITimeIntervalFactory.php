<?php


namespace App\Service;


use App\Entity\ITimeInterval;
use DateTimeImmutable;

interface ITimeIntervalFactory
{
    public function createFormTimestamp(int $startTime, int $endTime): ITimeInterval;

    public function create(
        string $startTime,
        int $period,
        string $units = ITimeInterval::UNITS_MINUTE,
        DateTimeImmutable $dt = null
    ): ITimeInterval;
}
