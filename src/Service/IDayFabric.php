<?php


namespace App\Service;


use App\Entity\IDay;
use DateTimeImmutable;

interface IDayFabric
{
    public function create(DateTimeImmutable $date): IDay;
}
