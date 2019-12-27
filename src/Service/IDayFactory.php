<?php


namespace App\Service;


use App\Entity\IDay;
use DateTimeImmutable;

interface IDayFactory
{
    public const MODE_ALL = 'all';
    public const MODE_WEEKENDS = 'weekends';
    public const MODE_WITHOUT_WEEKENDS = 'without_weekends';

    public function create(DateTimeImmutable $date, string $mode = self::MODE_ALL): ?IDay;

    /**
     * @param DateTimeImmutable $dtBegin
     * @param DateTimeImmutable $dtEnd
     * @param string $mode
     * @param IDay[] $days
     * @return IDay[]
     */
    public function createDaysByRange(DateTimeImmutable $dtBegin, DateTimeImmutable $dtEnd, $mode = self::MODE_ALL, array $days = []): array;

    /**
     * @param array $dates
     * @param IDay[] $days
     * @param string $mode
     * @return IDay[]
     */
    public function createFromDatesArray(array $dates, string $mode = self::MODE_ALL, array $days = []): array;
}
