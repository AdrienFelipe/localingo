<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Sample;

interface SampleRepositoryInterface
{
    /**
     * @param array<int, string> $words
     */
    public function fromDeclinationAndWords(string $declination, array $words): SampleCollection;
}
