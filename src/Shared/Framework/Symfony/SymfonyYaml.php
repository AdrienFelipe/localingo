<?php

namespace App\Shared\Framework\Symfony;

use App\Localingo\Infrastructure\File\Yaml\YamlInterface;
use Symfony\Component\Yaml\Yaml;

class SymfonyYaml implements YamlInterface
{
    #[\Override]
    public function dump(array $data): string
    {
        return Yaml::dump($data);
    }

    #[\Override]
    public function parseFile(string $filepath): mixed
    {
        return Yaml::parseFile($filepath);
    }
}
