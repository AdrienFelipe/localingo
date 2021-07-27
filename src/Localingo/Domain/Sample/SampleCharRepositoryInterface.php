<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Sample;

use App\Localingo\Domain\LocalData\LocalDataRawInterface;

interface SampleCharRepositoryInterface extends LocalDataRawInterface
{
    /**
     * @return string[]
     */
    public function loadList(): array;
}
