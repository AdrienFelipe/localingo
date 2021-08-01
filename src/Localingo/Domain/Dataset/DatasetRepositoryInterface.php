<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Dataset;

interface DatasetRepositoryInterface
{
    public function clearAllData(): void;

    public function saveFileHash(string $filename, string $fileHash): void;

    public function loadFileHash(string $filename): ?string;
}
