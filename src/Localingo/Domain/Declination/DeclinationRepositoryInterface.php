<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Declination;


interface DeclinationRepositoryInterface
{
    public function getRandom():string;
}