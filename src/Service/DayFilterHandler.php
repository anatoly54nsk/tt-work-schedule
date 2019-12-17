<?php


namespace App\Service;


abstract class DayFilterHandler implements IDayFilterHandler
{
    /**
     * @var IDayFilter
     */
    protected $filter;

    public function __construct(IDayFilter $filter)
    {
        $this->filter = $filter;
    }
}
