<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Word;

use App\Localingo\Domain\LocalData\LocalDataRawInterface;

interface WordRepositoryInterface extends LocalDataRawInterface
{
    /**
     * @return array<int, string>
     */
    public function getRandomAsList(int $count): array;
}
