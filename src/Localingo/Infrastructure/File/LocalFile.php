<?php

declare(strict_types=1);

namespace App\Localingo\Infrastructure\File;

use App\Shared\Domain\File\FileInterface;

abstract class LocalFile
{
    /**
     * Always returns a directory path with a trailing slash.
     */
    protected static function directory(): string
    {
        $path = $_ENV[FileInterface::KEY_FILES_DIR] ?? '';
        if (!is_dir($path)) {
            throw new \RuntimeException(sprintf('A valid directory must be set in $_ENV: %s="%s"', FileInterface::KEY_FILES_DIR, $path));
        }
        if (!is_writable($path)) {
            throw new \RuntimeException("'$path' is not writable");
        }

        return rtrim($path, '/').'/';
    }
}
