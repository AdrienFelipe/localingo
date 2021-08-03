<?php

namespace App\Localingo\Infrastructure\File\Yaml;

interface YamlInterface
{
    /**
     * @param mixed[] $data
     */
    public function dump(array $data): string;

    public function parseFile(string $filepath): mixed;
}
