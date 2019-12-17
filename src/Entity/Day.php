<?php


namespace App\Entity;


use DateTimeImmutable;

class Day implements IDay
{
    public $day;
    public $timeRanges;

    private $dt;

    public function __construct(DateTimeImmutable $date)
    {
        $this->dt = $date;
        $this->day = $date->format('Y-m-d');
    }

    public function getDayBeginTimestamp(): int
    {
        return $this->dt->modify('midnight')->getTimestamp();
    }
}
