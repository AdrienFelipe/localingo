<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Sample;

use App\Localingo\Domain\LocalData\LocalDataRawInterface;

interface SampleRepositoryInterface extends LocalDataRawInterface
{
    /**
     * @param ?string|string[] $words
     * @param ?string|string[] $declinations
     * @param ?string|string[] $genders
     * @param ?string|string[] $numbers
     * @param ?string|string[] $cases
     */
    public function loadMultiple(int $limit, mixed $words, mixed $declinations, mixed $genders = null, mixed $numbers = null, mixed $cases = null): SampleCollection;

    /**
     * @param string[] $words
     */
    public function fromSampleFilters(SampleCollection $sampleFilters, int $count, array $words = []): SampleCollection;
}
