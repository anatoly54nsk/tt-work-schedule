<?php


namespace App\Service;


use App\Entity\ITimeInterval;

interface ITimeIntervalFactory
{
    public function create(int $startTime, int $endTime): ITimeInterval;
}
