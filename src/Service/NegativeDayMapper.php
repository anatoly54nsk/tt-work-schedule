<?php


namespace App\Service;


use App\Entity\IDay;
use Exception;

class NegativeDayMapper extends DayMapper
{
    /**
     * @param IDay $day
     * @return IDay
     * @throws Exception
     */
    public function map(IDay $day): IDay
    {
        $day = parent::map($day);
        $intervals = $this->excludeIntervals($day->getTimeRanges(), $this->intervals, $day);
        $day->replaceTimeRanges($intervals);
        return $day;
    }
}
