<?php

namespace App\Localingo\Infrastructure\File\Yaml;

use App\Shared\Domain\File\FileInterface;
use Symfony\Component\Yaml\Yaml;

class YamlFile implements FileInterface
{
    protected const FILE_EXTENSION = '.local.yaml';

    public function clear(): void
    {
        // Find files.
        $files = glob(self::directory().'*'.self::FILE_EXTENSION);
        // Delete them.
        if ($files) {
            /** @psalm-suppress UnusedFunctionCall */
            array_map('unlink', $files);
        }
    }

    /**
     * Always returns a directory with path with a trailing slash.
     */
    protected static function directory(): string
    {
        $path = (string) ($_ENV[FileInterface::KEY_FILES_DIR] ?? '');
        if (!is_dir($path)) {
            throw new \RuntimeException(sprintf('A valid directory must be set in $_ENV: %s="%s"', FileInterface::KEY_FILES_DIR, $path));
        }
        if (!is_writable($path)) {
            throw new \RuntimeException("'$path' is not writable");
        }

        return rtrim($path, '/').'/';
    }

    /**
     * @param array<mixed> $data
     */
    protected static function writeYaml(string $filepath, array $data): void
    {
        $yaml = Yaml::dump($data);
        file_put_contents($filepath, $yaml);
    }

    /**
     * @return ?array<mixed> $data
     */
    protected static function readYaml(string $filepath): ?array
    {
        // Cleanly exit if file does not exist.
        if (!file_exists($filepath)) {
            return null;
        }

        return (array) Yaml::parseFile($filepath);
    }
}
