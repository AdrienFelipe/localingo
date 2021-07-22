<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Declination;

interface DeclinationRepositoryInterface
{
    /**
     * Creates all declinations from a list of strings. Deletes all previous data.
     *
     * @param array<int,string> $string_declinations
     */
    public function saveAllFromRawData(array $string_declinations): void;

    public function getRandom(): string;
}
