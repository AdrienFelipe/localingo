<?php

namespace App\Localingo\Infrastructure\File\Yaml;

use App\Localingo\Infrastructure\File\LocalFile;
use App\Shared\Domain\File\FileInterface;

class YamlFile extends LocalFile implements FileInterface
{
    protected const string FILE_EXTENSION = '.local.yaml';
    private YamlInterface $yaml;

    public function __construct(YamlInterface $yaml)
    {
        $this->yaml = $yaml;
    }

    #[\Override]
    public function clear(): void
    {
        // Every local file, not just YAML: this is the entry point tests use to reset state.
        $files = glob(self::directory().'*.local.*');
        // Delete them.
        if ($files !== false) {
            /** @psalm-suppress UnusedFunctionCall */
            array_map('unlink', $files);
        }
    }

    /**
     * @param array<mixed> $data
     */
    protected function writeYaml(string $filepath, array $data): void
    {
        $yaml = $this->yaml->dump($data);
        // Write then rename, so an interrupted write cannot truncate saved progress.
        $temporary = $filepath.'.tmp';
        if (file_put_contents($temporary, $yaml) === false || !rename($temporary, $filepath)) {
            throw new \RuntimeException("Could not write '$filepath'");
        }
    }

    /**
     * @return ?array<mixed> $data
     */
    protected function readYaml(string $filepath): ?array
    {
        // Cleanly exit if file does not exist.
        if (!file_exists($filepath)) {
            return null;
        }

        return (array) $this->yaml->parseFile($filepath);
    }
}
