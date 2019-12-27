<?php


namespace App\Service;


use App\Entity\IDay;
use DateTimeImmutable;
use Exception;

class PositiveCorporatePartyDayMapper extends DayMapper
{
    /**
     * @var DateTimeImmutable
     */
    private $date;

    /**
     * @param IDay $day
     * @return IDay
     * @throws Exception
     */
    public function map(IDay $day): IDay
    {
        $day = parent::map($day);
        if ($this->date) {
            $tsDay = $day->getDt()->modify('midnight')->getTimestamp();
            $tsMapper = $this->date->modify('midnight')->getTimestamp();
            if ($tsDay === $tsMapper) {
                $intervals = array_merge($day->getTimeRanges(), $this->intervals);
                if (!empty($intervals)) {
                    $intervals = $this->directSort($intervals);
                    $day->replaceTimeRanges($this->mergeIntervals($intervals, $day));
                }
            }
        }
        return $day;
    }

    public function setDate(DateTimeImmutable $dt)
    {
        $this->date = $dt->modify('midnight');
    }
}
