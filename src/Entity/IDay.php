<?php


namespace App\Entity;


use DateTimeImmutable;

interface IDay
{
    const DAY_FORMAT = 'Y-m-d';

    /** @return ITimeInterval[] */
    public function getTimeRanges(): array;

    /** @param ITimeInterval[] $intervals */
    public function replaceTimeRanges(array $intervals);

    public function getDayBeginTimestamp(): int;

    public function getDay(): string;

    public function getDt(): DateTimeImmutable;
}
