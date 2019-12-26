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
        $day = parent::map($day);
        $mapperRanges = $this->directSort($this->intervals);
        $mapperRanges = $this->mergeIntervals($mapperRanges, $day);
        $intervals = $this->excludeIntervals($day->getTimeRanges(), $mapperRanges, $day);
        $day->replaceTimeRanges($intervals);
        return $day;
    }

    /**
     * @param ITimeInterval[] $positiveIntervals
     * @param ITimeInterval[] $negativeIntervals
     * @param IDay $day
     * @return ITimeInterval[]
     */
    private function excludeIntervals(array $positiveIntervals, array $negativeIntervals, IDay $day): array
    {
        return array_reduce($positiveIntervals, function ($result, $positiveInterval) use (&$negativeIntervals, $day) {
            /** @var ITimeInterval $positiveInterval */
            $positiveInterval->setDate($day->getDt());
            if (count($negativeIntervals) > 0) {
                foreach ($negativeIntervals as $negativeInterval) {
                    $negativeInterval->setDate($day->getDt());
                    $negativeIntervals = $this->deleteNegInterval(
                        $positiveInterval,
                        $negativeInterval,
                        $negativeIntervals
                    );
                    $result = array_merge($result, $this->getIterationResult($positiveInterval, $negativeInterval));
                    $positiveInterval = $this->changePositiveInterval($positiveInterval, $negativeInterval);
                    if (count($negativeIntervals) === 0 && $positiveInterval) {
                        $result[] = $positiveInterval;
                    }
                    if ($positiveInterval) {
                        continue;
                    }
                    break;
                }
            } else {
                $result[] = $this->intervalFactory->createFormTimestamp($positiveInterval->getStart(), $positiveInterval->getEnd());
            }
            return $result;
        }, []);
    }

    /**
     * @param ITimeInterval $positiveInterval
     * @param ITimeInterval $negativeInterval
     * @param array $negativeIntervals
     * @return ITimeInterval[]
     */
    private function deleteNegInterval(
        ITimeInterval $positiveInterval,
        ITimeInterval $negativeInterval,
        array $negativeIntervals
    ): array
    {
        if (
            $negativeInterval->getEnd() <= $positiveInterval->getStart()
            || ($negativeInterval->getStart() <= $positiveInterval->getStart()
                && $negativeInterval->getEnd() < $positiveInterval->getEnd())
            || ($positiveInterval->getStart() < $negativeInterval->getStart()
                && $negativeInterval->getEnd() < $positiveInterval->getEnd())
        ) {
            array_shift($negativeIntervals);
        }
        return $negativeIntervals;
    }

    /**
     * @param ITimeInterval $positiveInterval
     * @param ITimeInterval $negativeInterval
     * @return ITimeInterval[]
     * @throws Exception
     */
    private function getIterationResult(ITimeInterval $positiveInterval, ITimeInterval $negativeInterval)
    {
        $result = [];
        if ($positiveInterval->getEnd() <= $negativeInterval->getStart()) {
            $result[] = $this->intervalFactory->createFormTimestamp($positiveInterval->getStart(), $positiveInterval->getEnd());
        } elseif ($positiveInterval->getStart() < $negativeInterval->getStart()) {
            $result[] = $this->intervalFactory->createFormTimestamp($positiveInterval->getStart(), $negativeInterval->getStart());
        }
        return $result;
    }

    /**
     * @param ITimeInterval $positiveInterval
     * @param ITimeInterval $negativeInterval
     * @return ITimeInterval|null
     * @throws Exception
     */
    private function changePositiveInterval(ITimeInterval $positiveInterval, ITimeInterval $negativeInterval)
    {
        $newInterval = null;
        if ($negativeInterval->getEnd() < $positiveInterval->getEnd()) {
            if ($negativeInterval->getEnd() <= $positiveInterval->getStart()) {
                $newInterval = $this->intervalFactory->createFormTimestamp($positiveInterval->getStart(), $positiveInterval->getEnd());
            } else {
                $newInterval = $this->intervalFactory->createFormTimestamp($negativeInterval->getEnd(), $positiveInterval->getEnd());
            }
        }
        return $newInterval;
    }
}
