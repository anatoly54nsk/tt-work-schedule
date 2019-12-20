<?php


namespace App\Service;


use App\Entity\ITimeInterval;
use App\Entity\TimeInterval;
use DateTimeImmutable;
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
     * @param ITimeInterval[] $intervals
     * @param IDayMapper|null $previous
     */
    public function __construct(array $intervals, IDayMapper $previous = null)
    {
        $this->intervals = $intervals;
        $this->previous = $previous;
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
     * @return ITimeInterval[]
     * @throws Exception
     */
    protected function mergeIntervals(array $intervals)
    {
        $result = [];
        $current = array_shift($intervals);
        foreach ($intervals as $interval) {
            if ($interval->getStart() > $current->getEnd()) {
                $result[] = $current;
                $current = $interval;
                continue;
            }
            $intervalStart = $current->getStart();
            $intervalEnd = $current->getEnd();
            if ($current->getEnd() < $interval->getEnd()) {
                $intervalEnd = $interval->getEnd();
            }
            $current = $this->getNewInterval($intervalStart, $intervalEnd);
        }
        $result[] = $current;
        return $result;
    }

    protected function getNewInterval(int $startTimestamp, int $endTimestamp): ITimeInterval
    {
        $dt = new DateTimeImmutable();
        $start = $dt->setTimestamp($startTimestamp);
        $end = $dt->setTimestamp($endTimestamp);
        return new TimeInterval($start, $end);
    }
}
