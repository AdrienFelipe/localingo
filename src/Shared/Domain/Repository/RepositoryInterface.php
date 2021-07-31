<?php

declare(strict_types=1);

namespace App\Shared\Domain\Repository;

interface RepositoryInterface
{
    public function clear(): void;
}
