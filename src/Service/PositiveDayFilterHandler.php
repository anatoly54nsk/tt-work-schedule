<?php


namespace App\Service;


class PositiveDayFilterHandler extends DayFilterHandler
{
    /**
     * @inheritDoc
     */
    public function filter(array $days): array
    {
        if (!$this->filter->isEmpty()) {
            $days = array_filter($days, function ($day) {
                return $this->filter->isDesired($day);
            });
        }
        return $days;
    }
}
