<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Sample;

use App\Localingo\Domain\Dataset\DatasetRawInterface;

interface SampleCharRepositoryInterface extends DatasetRawInterface
{
    /**
     * @return string[]
     */
    public function loadList(): array;
}
