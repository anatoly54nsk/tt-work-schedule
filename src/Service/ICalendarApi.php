<?php


namespace App\Service;


use DateTimeImmutable;

interface ICalendarApi
{
    /**
     * @param DateTimeImmutable $start
     * @param DateTimeImmutable $end
     * @return DateTimeImmutable[]
     */
    public function getHolidays(DateTimeImmutable $start, DateTimeImmutable $end): array;
}
