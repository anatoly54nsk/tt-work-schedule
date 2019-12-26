<?php


namespace App\Entity;


use DateTimeImmutable;
use JsonSerializable;

/**
 * @property string day
 * @property DateTimeImmutable dt
 */
class Day implements IDay, JsonSerializable
{
    private $timeRanges = [];

    private $dt;

    public function __construct(DateTimeImmutable $date)
    {
        $this->dt = $date;
    }

    public function getDayBeginTimestamp(): int
    {
        return $this->dt->modify('midnight')->getTimestamp();
    }

    /**
     * @inheritDoc
     */
    public function getTimeRanges(): array
    {
        return $this->timeRanges;
    }

    /**
     * @inheritDoc
     */
    public function replaceTimeRanges(array $intervals)
    {
        $this->timeRanges = $intervals;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return ['day' => $this->getDay(), 'timeRanges' => $this->timeRanges];
    }

    public function getDay(): string
    {
        return $this->dt->format(self::DAY_FORMAT);
    }

    public function getDt(): DateTimeImmutable
    {
        return $this->dt;
    }
}
