<?php


namespace App\Service;


use DateTimeImmutable;

interface ICalendarApi
{
    public function getHolidays(DateTimeImmutable $start, DateTimeImmutable $end);
}
