<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Declination;

use App\Localingo\Domain\LocalData\LocalDataRawInterface;

interface DeclinationRepositoryInterface extends LocalDataRawInterface
{
    public function getRandom(): string;
}
