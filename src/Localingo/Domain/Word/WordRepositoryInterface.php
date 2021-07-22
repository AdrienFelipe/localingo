<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Word;

interface WordRepositoryInterface
{
    /**
     * Creates all words from a list of strings. Deletes all previous data.
     *
     * @param array<int, string> $string_words
     */
    public function saveAllFromRawData(array $string_words): void;

    /**
     * @return array<int, string>
     */
    public function getRandomAsList(int $count): array;
}
