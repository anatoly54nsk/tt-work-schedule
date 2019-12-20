<?php


namespace App\Service;


use App\Entity\IDay;

interface IDayMapper
{
    public function map(IDay $day): IDay;

    public function setPrevious(IDayMapper $previous);
}
