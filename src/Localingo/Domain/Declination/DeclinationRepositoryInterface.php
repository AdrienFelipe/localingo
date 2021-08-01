<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Declination;

use App\Localingo\Domain\Dataset\DatasetRawInterface;

interface DeclinationRepositoryInterface extends DatasetRawInterface
{
    /**
     * @param int      $limit   if 0, returns all
     * @param string[] $exclude
     *
     * @return string[]
     */
    public function getByPriority(int $limit = 0, array $exclude = []): array;
}
