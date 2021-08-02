<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Sample;

use App\Localingo\Domain\Dataset\DatasetRawInterface;

interface SampleRepositoryInterface extends DatasetRawInterface
{
    /**
     * @param int                  $limit        if 0 load all
     * @param string|string[]|null $words
     * @param string|string[]|null $declinations
     * @param string|string[]|null $genders
     * @param string|string[]|null $numbers
     * @param string|string[]|null $cases
     */
    public function loadMultiple(int $limit = 0, ?SampleCollection $exclude = null, mixed $words = null, mixed $declinations = null, mixed $genders = null, mixed $numbers = null, mixed $cases = null): SampleCollection;
}
