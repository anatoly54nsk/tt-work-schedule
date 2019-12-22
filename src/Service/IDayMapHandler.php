<?php


namespace App\Service;


use App\Entity\IDay;

interface IDayMapHandler
{
    /**
     * @param IDay[] $days
     * @return IDay[]
     */
    public function map(array $days): array;
}
