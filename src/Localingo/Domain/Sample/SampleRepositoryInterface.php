<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Sample;

interface SampleRepositoryInterface
{
    /**
     * @param string[] $values
     */
    public function saveFromRawData(array $values): void;

    /**
     * @param array<int, string> $words
     */
    public function fromDeclinationAndWords(string $declination, array $words): SampleCollection;
}
