<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Declination;

use App\Localingo\Domain\LocalData\LocalDataRawInterface;

interface DeclinationRepositoryInterface extends LocalDataRawInterface
{
    /**
     * @param int      $limit   if 0, returns all
     * @param string[] $exclude
     *
     * @return string[]
     */
    public function getByPriority(int $limit = 0, array $exclude = []): array;
}
