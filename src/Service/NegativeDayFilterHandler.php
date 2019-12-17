<?php


namespace App\Service;


class NegativeDayFilterHandler extends DayFilterHandler
{
    /**
     * @inheritDoc
     */
    public function filter(array $days): array
    {
        if (!$this->filter->isEmpty()) {
            $days = array_values(array_filter($days, function ($day) {
                return !$this->filter->isDesired($day);
            }));
        }
        return $days;
    }
}
