<?php


namespace App\Service;


use App\Entity\IDay;
use DateTimeImmutable;
use Exception;

class NegativeCorporatePartyDayMapper extends DayMapper
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
                $mapperRanges = $this->directSort($this->intervals);
                $mapperRanges = $this->mergeIntervals($mapperRanges, $day);
                $intervals = $this->excludeIntervals($day->getTimeRanges(), $mapperRanges, $day);
                $day->replaceTimeRanges($intervals);
            }
        }
        return $day;
    }

    public function setDate(DateTimeImmutable $dt)
    {
        $this->date = $dt->modify('midnight');
    }
}
