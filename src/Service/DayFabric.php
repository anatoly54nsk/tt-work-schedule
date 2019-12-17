<?php


namespace App\Service;


use App\Entity\Day;
use App\Entity\IDay;
use DateTimeImmutable;

class DayFabric implements IDayFabric
{
    /**
     * @param DateTimeImmutable $date
     * @return Day|IDay
     */
    public function create(DateTimeImmutable $date): IDay
    {
        return new Day($date);
    }
}
