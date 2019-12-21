<?php


namespace App\Entity;


use DateTimeImmutable;
use JsonSerializable;

interface ITimeInterval extends JsonSerializable
{
    const FORMAT_DATE = 'Y-m-d';
    const FORMAT_FULL = 'Y-m-d H:i';
    const FORMAT_TIME = 'H:i';
    const UNITS_MINUTE = 'minute';
    const UNITS_HOUR = 'hour';

    public function getStart(): int;

    public function getEnd(): int;

    public function setDate(DateTimeImmutable $dt);
}
