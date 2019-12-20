<?php


namespace App\Entity;


use JsonSerializable;

interface ITimeInterval extends JsonSerializable
{
    const TIME_FORMAT = 'H:i';

    public function getStart(): int;

    public function getEnd(): int;
}
