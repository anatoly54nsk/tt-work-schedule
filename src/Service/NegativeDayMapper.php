<?php


namespace App\Service;


use App\Entity\IDay;
use App\Entity\ITimeInterval;
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
        if ($this->previous !== null) {
            $day = $this->previous->map($day);
        }
        $negativeIntervals = $this->intervals;
        $intervals = $this->excludeVariants($day->getTimeRanges(), $negativeIntervals);
        $day->replaceTimeRanges($intervals);
        return $day;
    }

    /**
     * @param ITimeInterval[] $positiveIntervals
     * @param ITimeInterval[] $negativeIntervals
     * @return ITimeInterval[]
     * @throws Exception
     */
    private function excludeVariants(array $positiveIntervals, array $negativeIntervals): array
    {
        return array_reduce($positiveIntervals, function ($result, $positiveInterval) use (&$negativeIntervals) {
            $resultIntervals = [];
            /** @var ITimeInterval $positiveInterval */
            foreach ($negativeIntervals as $negativeInterval) {
                $positiveIntervalStart = $positiveInterval->getStart();
                $positiveIntervalEnd = $positiveInterval->getEnd();
                $negativeIntervalStart = $negativeInterval->getStart();
                $negativeIntervalEnd = $negativeInterval->getEnd();
                if ($positiveIntervalEnd <= $negativeIntervalStart) {
                    $resultIntervals[] = $positiveInterval;
                    break;
                }
                if ($negativeIntervalStart <= $positiveIntervalStart && $positiveIntervalEnd <= $negativeIntervalEnd) {
                    break;
                }
                if ($negativeIntervalStart <= $positiveIntervalStart && $negativeIntervalEnd < $positiveIntervalEnd) {
                    $positiveInterval = $this->getNewInterval($negativeIntervalEnd, $positiveIntervalEnd);
                    array_shift($negativeIntervals);
                    if (count($negativeIntervals) === 0) {
                        $resultIntervals[] = $positiveInterval;
                    }
                    continue;
                }
                $resultIntervals[] = $this->getNewInterval($positiveIntervalStart, $negativeIntervalStart);
                if ($positiveIntervalStart < $negativeIntervalStart && $negativeIntervalEnd < $positiveIntervalEnd) {
                    $positiveInterval = $this->getNewInterval($negativeIntervalEnd, $positiveIntervalEnd);
                    array_shift($negativeIntervals);
                    if (count($negativeIntervals) === 0) {
                        $resultIntervals[] = $positiveInterval;
                    }
                    continue;
                }
                break;
            }
            if (count($resultIntervals) > 0) {
                $result = array_merge($result, $resultIntervals);
            }
            return $result;
        }, []);
    }
}
