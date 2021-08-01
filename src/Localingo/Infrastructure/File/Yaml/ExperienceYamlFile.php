<?php

declare(strict_types=1);

namespace App\Localingo\Infrastructure\File\Yaml;

use App\Localingo\Domain\Experience\Experience;
use App\Localingo\Domain\Experience\ExperienceFileInterface;
use App\Localingo\Domain\User\User;

class ExperienceYamlFile extends YamlFile implements ExperienceFileInterface
{
    private const FILENAME = '-experience'.self::FILE_EXTENSION;

    public function read(User $user): ?Experience
    {
        if (!$data = self::readYaml($this->filepath($user))) {
            return null;
        }

        $experience = new Experience($user);
        /** @var array<string, mixed> $data */
        $experience->unserialize($data);

        return $experience;
    }

    public function write(Experience $experience): void
    {
        self::writeYaml($this->filepath($experience->getUser()), $experience->serialize());
    }

    private function filepath(User $user): string
    {
        return self::directory().$user->getId().self::FILENAME;
    }
}
