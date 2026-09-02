<?php

declare(strict_types=1);

namespace App\Localingo\Domain\History;

use App\Localingo\Domain\User\User;

interface HistoryFileInterface
{
    public function append(HistoryEntry $entry): void;

    /**
     * @return HistoryEntry[] most recent first
     */
    public function readLast(User $user, int $limit): array;
}
