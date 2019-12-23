<?php


namespace App\Service;


use App\Entity\IDay;
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
                    $current->setDate($day->dt);
                    $interval->setDate($day->dt);
                }
                if ($current->getEnd() < $interval->getStart()) {
                    $result[] = $this->getNewInterval($current->getStart(), $current->getEnd());
                    $current = $this->getNewInterval($interval->getStart(), $interval->getEnd());
                    continue;
                }
                $intervalStart = $current->getStart() < $interval->getStart() ? $current->getStart() : $interval->getStart();
                $intervalEnd = $current->getEnd() < $interval->getEnd() ? $interval->getEnd() : $current->getEnd();
                $current = $this->getNewInterval($intervalStart, $intervalEnd);
            }
            if ($day) {
                $current->setDate($day->dt);
            }
            $result[] = $this->getNewInterval($current->getStart(), $current->getEnd());
        }
        return $result;
    }

    /**
     * @param int $startTimestamp
     * @param int $endTimestamp
     * @return ITimeInterval
     * @throws Exception
     */
    protected function getNewInterval(int $startTimestamp, int $endTimestamp): ITimeInterval
    {
        $dt = new DateTimeImmutable();
        $start = $dt->setTimestamp($startTimestamp);
        $minutes = ($endTimestamp - $startTimestamp) / 60;
        return new TimeInterval($start->format(
            ITimeInterval::FORMAT_TIME),
            $minutes,
            ITimeInterval::UNITS_MINUTE,
            $start->modify('midnight')
        );
    }
}
