<?php


namespace App\Service;


use App\Entity\IDay;
use App\Entity\ITimeInterval;
use Exception;

abstract class DayMapper implements IDayMapper
{
    /**
     * @var ITimeInterval[] array
     */
    protected $intervals;
    /**
     * @var IDayMapper
     */
    protected $previous;
    /**
     * @var ITimeIntervalFactory
     */
    protected $intervalFactory;

    /**
     * @param ITimeInterval[] $intervals
     * @param ITimeIntervalFactory $intervalFactory
     * @param IDayMapper|null $previous
     */
    public function __construct(array $intervals, ITimeIntervalFactory $intervalFactory, IDayMapper $previous = null)
    {
        $this->intervals = $intervals;
        $this->previous = $previous;
        $this->intervalFactory = $intervalFactory;
    }

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
        $dayRanges = $this->directSort($day->getTimeRanges());
        $day->replaceTimeRanges($this->mergeIntervals($dayRanges, $day));
        $this->intervals = $this->directSort($this->intervals);
        $this->intervals = $this->mergeIntervals($this->intervals, $day);
        return $day;
    }

    public function setPrevious(IDayMapper $previous)
    {
        $this->previous = $previous;
    }

    /**
     * @param ITimeInterval[] $intervals
     * @return ITimeInterval[]
     */
    protected function directSort(array $intervals): array
    {
        usort($intervals, function ($intervalA, $intervalB) {
            /** @var ITimeInterval $intervalA */
            /** @var ITimeInterval $intervalB */
            return $intervalA->getStart() - $intervalB->getStart();
        });
        return $intervals;
    }

    /**
     * @param ITimeInterval[] $intervals
     * @param IDay $day
     * @return ITimeInterval[]
     * @throws Exception
     */
    protected function mergeIntervals(array $intervals, IDay $day = null)
    {
        $result = [];
        if (count($intervals) > 0) {
            $current = array_shift($intervals);
            foreach ($intervals as $interval) {
                if ($day) {
                    $current->setDate($day->getDt());
                    $interval->setDate($day->getDt());
                }
                if ($current->getEnd() < $interval->getStart()) {
                    $result[] = $this->intervalFactory->createFormTimestamp($current->getStart(), $current->getEnd());
                    $current = $this->intervalFactory->createFormTimestamp($interval->getStart(), $interval->getEnd());
                    continue;
                }
                $intervalStart = $current->getStart() < $interval->getStart() ?
                    $current->getStart() : $interval->getStart();
                $intervalEnd = $current->getEnd() < $interval->getEnd() ? $interval->getEnd() : $current->getEnd();
                $current = $this->intervalFactory->createFormTimestamp($intervalStart, $intervalEnd);
            }
            if ($day) {
                $current->setDate($day->getDt());
            }
            $result[] = $this->intervalFactory->createFormTimestamp($current->getStart(), $current->getEnd());
        }
        return $result;
    }

    /**
     * @param ITimeInterval[] $positiveIntervals
     * @param ITimeInterval[] $negativeIntervals
     * @param IDay $day
     * @return ITimeInterval[]
     */
    protected function excludeIntervals(array $positiveIntervals, array $negativeIntervals, IDay $day): array
    {
        return array_reduce($positiveIntervals, function ($result, $positiveInterval) use (&$negativeIntervals, $day) {
            /** @var ITimeInterval $positiveInterval */
            $positiveInterval->setDate($day->getDt());
            if (!empty($negativeIntervals)) {
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
    protected function deleteNegInterval(
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
    protected function getIterationResult(ITimeInterval $positiveInterval, ITimeInterval $negativeInterval)
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
    protected function changePositiveInterval(ITimeInterval $positiveInterval, ITimeInterval $negativeInterval)
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
