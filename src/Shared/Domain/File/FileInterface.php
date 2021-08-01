<?php

declare(strict_types=1);

namespace App\Shared\Domain\File;

interface FileInterface
{
    public const KEY_FILES_DIR = 'LOCAL_FILES_DIR';

    public function clear(): void;
}
