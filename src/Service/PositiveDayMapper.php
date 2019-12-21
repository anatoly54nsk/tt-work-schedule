<?php


namespace App\Service;


use App\Entity\IDay;
use Exception;

class PositiveDayMapper extends DayMapper
{
    /**
     * @param IDay $day
     * @return IDay
     * @throws Exception
     */
    public function map(IDay $day): IDay
    {
        if ($this->previous !== null) {
            $day = $this->previous->map($day);
        }
        $intervals = array_merge($day->getTimeRanges(), $this->intervals);
        if (!empty($intervals)) {
            $intervals = $this->directSort($intervals);
            $day->replaceTimeRanges($this->mergeIntervals($intervals, $day));
        }
        return $day;
    }
}
