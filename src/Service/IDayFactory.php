<?php


namespace App\Service;


use App\Entity\IDay;
use DateTimeImmutable;

interface IDayFactory
{
    public function create(DateTimeImmutable $date): IDay;
}
