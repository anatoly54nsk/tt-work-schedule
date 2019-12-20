<?php


namespace App\Service;


use App\Entity\IDay;

class DayMapHandler implements IDayMapHandler
{
    /**
     * @var IDayMapper
     */
    private $mapper;

    public function __construct(IDayMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * @inheritDoc
     */
    public function map(array $days): array
    {
        $days = array_map(function ($day) {
            /** @var IDay $day */
            return $this->mapper->map($day);
        }, $days);
        return $days;
    }
}
