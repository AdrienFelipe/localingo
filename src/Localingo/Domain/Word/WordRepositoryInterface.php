<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Word;

use App\Localingo\Domain\LocalData\LocalDataRawInterface;

interface WordRepositoryInterface extends LocalDataRawInterface
{
    /**
     * @param string[] $exclude
     *
     * @return string[]
     */
    public function getByPriority(int $limit, array $exclude = []): array;
}
