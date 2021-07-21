<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Word;


interface WordRepositoryInterface
{
    /**
     * @return array<int, string>
     */
    public function getRandomAsList(int $count): array;
}