<?php


namespace App\Service;


use App\Entity\Day;
use App\Entity\IDay;
use DateTimeImmutable;
use PHPUnit\Runner\Exception;

class DayFactory implements IDayFactory
{
    /**
     * @param DateTimeImmutable $date
     * @param string $mode
     * @return Day|IDay
     */
    public function create(DateTimeImmutable $date, string $mode = self::MODE_ALL): ?IDay
    {
        $day = null;
        switch ($mode) {
            case self::MODE_ALL:
                $day = new Day($date);
                break;
            case self::MODE_WITHOUT_WEEKENDS:
                if ((int)$date->format('w') !== 0 && (int)$date->format('w') !== 6) {
                    $day = new Day($date);
                }
                break;
            case self::MODE_WEEKENDS:
                if ((int)$date->format('w') === 0 || (int)$date->format('w') === 6) {
                    $day = new Day($date);
                }
                break;
            default:
                throw new Exception('Undefined mode.');
                break;
        }
        return $day;
    }


    /**
     * @param DateTimeImmutable $dtBegin
     * @param DateTimeImmutable $dtEnd
     * @param string $mode
     * @param array $days
     * @return IDay[]
     */
    public function createDaysByRange(
        DateTimeImmutable $dtBegin,
        DateTimeImmutable $dtEnd,
        $mode = self::MODE_ALL,
        $days = []
    ): array
    {
        if ($dtBegin->getTimestamp() <= $dtEnd->getTimestamp()) {
            $start = $dtBegin;
            $end = $dtEnd->getTimestamp();
            while ($start->getTimestamp() <= $end) {
                $day = $this->create($start, $mode);
                if (isset($day)) {
                    $days[] = $day;
                }
                $start = $start->modify('+1 day');
            }
        }
        return $days;
    }

    /**
     * @inheritDoc
     */
    public function createFromDatesArray(array $dates, string $mode = self::MODE_ALL, array $days = []): array
    {
        if (!empty($dates)) {
            foreach ($dates as $date) {
                $day = $this->create($date, $mode);
                if (isset($day)) {
                    $days[] = $day;
                }
            }
        }
        return $days;
    }
}
