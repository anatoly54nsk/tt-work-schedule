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
}
