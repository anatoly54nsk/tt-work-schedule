<?php


namespace App\Service;


use App\Entity\IDay;
use DateTimeImmutable;

class DayFilter implements IDayFilter
{
    /**
     * @var DateTimeImmutable[]
     */
    private $days;

    /**
     * WeekendFilter constructor.
     * @param DateTimeImmutable[] $days
     */
    public function __construct(array $days = [])
    {
        $this->days = $days;
    }

    public function isDesired(IDay $day): bool
    {
        $filtered = array_filter($this->days, function ($dayInFilter) use ($day) {
            /** @var DateTimeImmutable $dayInFilter */
            return $dayInFilter->modify('midnight')->getTimestamp() === $day->getDayBeginTimestamp();
        });
        return count($filtered) > 0;
    }

    public function isEmpty(): bool
    {
        return count($this->days) === 0;
    }
}
