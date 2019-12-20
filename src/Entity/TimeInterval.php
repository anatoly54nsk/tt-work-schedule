<?php


namespace App\Entity;


use DateTimeImmutable;

class TimeInterval implements ITimeInterval
{
    /**
     * @var DateTimeImmutable
     */
    private $start;
    /**
     * @var DateTimeImmutable
     */
    private $end;

    public function __construct(DateTimeImmutable $start, DateTimeImmutable $end)
    {
        $this->start = $start;
        $this->end = $end;
    }

    public function getStart(): int
    {
        return $this->start->getTimestamp();
    }

    public function getEnd(): int
    {
        return $this->end->getTimestamp();
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'start' => $this->start->format(ITimeInterval::TIME_FORMAT),
            'end' => $this->end->format(ITimeInterval::TIME_FORMAT)
        ];
    }
}
