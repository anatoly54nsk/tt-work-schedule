<?php


namespace App\Service;


use App\Entity\IDay;
use DateTimeImmutable;

class CorporatePartyDayMapper extends NegativeDayMapper
{
    /**
     * @var DateTimeImmutable
     */
    private $date;

    public function map(IDay $day): IDay
    {
        if (isset($this->date) && $day->getDt()->format(IDay::DAY_FORMAT) === $this->date->format(IDay::DAY_FORMAT)) {
            $day = parent::map($day);
        } else {
            $dayRanges = $this->directSort($day->getTimeRanges());
            $day->replaceTimeRanges($this->mergeIntervals($dayRanges, $day));
        }
        return $day;
    }

    public function setDate(DateTimeImmutable $dt)
    {
        $this->date = $dt->modify('midnight');
    }
}
