<?php


namespace App\Entity;


interface IDay
{
    const DAY_FORMAT = 'Y-m-d';

    /** @return ITimeInterval[] */
    public function getTimeRanges(): array;

    /** @param ITimeInterval[] $intervals */
    public function replaceTimeRanges(array $intervals);

    public function getDayBeginTimestamp(): int;
}
