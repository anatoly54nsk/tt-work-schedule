<?php


namespace App\Entity;


use DateTimeImmutable;
use Exception;

class TimeInterval implements ITimeInterval
{
    /**
     * @var DateTimeImmutable
     */
    private $dt;

    /**
     * @var string
     */
    private $startTime;
    /**
     * @var int
     */
    private $period;
    /**
     * @var string
     */
    private $units;

    /**
     * TimeInterval constructor.
     * @param string $startTime
     * @param int $period
     * @param string $units
     * @param DateTimeImmutable | null $dt
     * @throws Exception
     */
    public function __construct(
        string $startTime,
        int $period,
        string $units = self::UNITS_MINUTE,
        DateTimeImmutable $dt = null
    )
    {
        $this->startTime = $startTime;
        $this->period = $period;
        $this->units = $units;
        $this->dt = isset($dt) ? $dt : new DateTimeImmutable();
    }

    public function getStart(): int
    {
        return $this->getStartDate()->getTimestamp();
    }

    public function getEnd(): int
    {
        return $this->getStartDate()->modify("+{$this->period} {$this->units}")->getTimestamp();
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'start' => $this->getStartDate()->format(ITimeInterval::FORMAT_TIME),
            'end' => $this->getStartDate()->modify("+{$this->period} {$this->units}")
                ->format(ITimeInterval::FORMAT_TIME)
        ];
    }

    private function getStartDate(): DateTimeImmutable
    {
        $curDate = $this->dt->format(self::FORMAT_DATE);
        $timestamp =
            (DateTimeImmutable::createFromFormat(self::FORMAT_FULL, "{$curDate} {$this->startTime}"))
                ->getTimestamp();
        return $this->dt->setTimestamp($timestamp);
    }

    public function setDate(DateTimeImmutable $dt)
    {
        $this->dt = $dt;
    }
}
