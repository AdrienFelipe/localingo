<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Sample;

use App\Localingo\Domain\Dataset\DatasetRawInterface;

interface SampleCaseRepositoryInterface extends DatasetRawInterface
{
    /**
     * @param string[] $declinations
     */
    public function getCases(array $declinations): SampleCollection;
}
