<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Sample;

use App\Localingo\Domain\LocalData\LocalDataRawInterface;

interface SampleCaseRepositoryInterface extends LocalDataRawInterface
{
    /**
     * @param string[] $declinations
     */
    public function getCases(array $declinations): SampleCollection;
}
