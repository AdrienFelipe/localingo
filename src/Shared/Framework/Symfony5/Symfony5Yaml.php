<?php

namespace App\Shared\Framework\Symfony5;

use App\Localingo\Infrastructure\File\Yaml\YamlInterface;
use Symfony\Component\Yaml\Yaml;

class Symfony5Yaml implements YamlInterface
{
    public function dump(array $data): string
    {
        return Yaml::dump($data);
    }

    public function parseFile(string $filepath): mixed
    {
        return Yaml::parseFile($filepath);
    }
}
