<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Sample;

use App\Localingo\Domain\LocalData\LocalDataRawInterface;

interface SampleRepositoryInterface extends LocalDataRawInterface
{
    /**
     * @param array<int, string> $words
     */
    public function fromDeclinationAndWords(string $declination, array $words): SampleCollection;
}
