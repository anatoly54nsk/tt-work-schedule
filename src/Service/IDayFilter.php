<?php


namespace App\Service;


use App\Entity\IDay;

interface IDayFilter
{
    public function isDesired(IDay $day): bool;

    public function isEmpty(): bool;
}
