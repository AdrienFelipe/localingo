<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Sample;

use App\Localingo\Domain\LocalData\LocalDataRawInterface;

interface SampleRepositoryInterface extends LocalDataRawInterface
{
    /**
     * @param string|string[]|null $words
     * @param string|string[]|null $declinations
     * @param string|string[]|null $genders
     * @param string|string[]|null $numbers
     * @param string|string[]|null $cases
     */
    public function loadMultiple(int $limit, mixed $words, mixed $declinations, mixed $genders = null, mixed $numbers = null, mixed $cases = null): SampleCollection;
}
