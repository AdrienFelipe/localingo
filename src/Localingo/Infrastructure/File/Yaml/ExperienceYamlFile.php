<?php

declare(strict_types=1);

namespace App\Localingo\Infrastructure\File\Yaml;

use App\Localingo\Domain\Experience\Experience;
use App\Localingo\Domain\Experience\ExperienceFileInterface;
use App\Localingo\Domain\User\User;
use Symfony\Component\Yaml\Yaml;

class ExperienceYamlFile implements ExperienceFileInterface
{
    private const FILE_DIR = '/app/files/';
    private const FILE_NAME = '-experience.local.yaml';

    public function read(User $user): ?Experience
    {
        $filepath = $this->filepath($user);
        // Cleanly exit if file does not exist.
        if (!file_exists($filepath)) {
            return null;
        }

        $experience = new Experience($user);
        /** @var array<string, mixed> $data */
        $data = (array) Yaml::parseFile($filepath);
        $experience->unserialize($data);

        return $experience;
    }

    public function write(Experience $experience): void
    {
        $yaml = Yaml::dump($experience->serialize());
        file_put_contents($this->filepath($experience->getUser()), $yaml);
    }

    private function filepath(User $user): string
    {
        return self::FILE_DIR.$user->getId().self::FILE_NAME;
    }
}
