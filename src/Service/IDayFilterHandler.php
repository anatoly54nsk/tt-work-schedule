<?php


namespace App\Service;


use App\Entity\IDay;

interface IDayFilterHandler
{
    /**
     * @param IDay[] $days
     * @return IDay[]
     */
    public function filter(array $days): array;
}
