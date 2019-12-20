<?php


namespace App\Entity;


use DateTimeImmutable;
use Exception;
use JsonSerializable;

/**
 * @property string day
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
     * @param $name
     * @return string
     * @throws Exception
     */
    public function __get($name)
    {
        switch ($name) {
            case 'day':
                $value = $this->dt->format(self::DAY_FORMAT);
                break;
            default:
                throw new Exception("Field '{$name}' not exists in Day entity.");
        }
        return $value;
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
        return ['day' => $this->day, 'timeRanges' => $this->timeRanges];
    }
}
