<?php


namespace App\Service;


use App\Entity\IDay;

interface IDayMapHandler
{
    /**
     * @param IDay[] $days
     * @return array
     */
    public function map(array $days): array;
}
