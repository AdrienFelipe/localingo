<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Sample;

use App\Localingo\Domain\LocalData\LocalDataRawInterface;

interface SampleRepositoryInterface extends LocalDataRawInterface
{
    /**
     * @param string[] $declinations
     * @param string[] $words
     */
    public function fromDeclinationAndWords(array $declinations, array $words, int $count): SampleCollection;

    /**
     * @param string[] $words
     */
    public function fromSampleFilters(SampleCollection $sampleFilters, int $count, array $words): SampleCollection;
}
