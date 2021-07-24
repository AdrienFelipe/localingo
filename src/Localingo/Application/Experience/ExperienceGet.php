<?php

declare(strict_types=1);

namespace App\Localingo\Application\Experience;

use App\Localingo\Domain\Experience\Experience;
use App\Localingo\Domain\Experience\ExperienceFileInterface;
use App\Localingo\Domain\Experience\ExperienceRepositoryInterface;
use App\Localingo\Domain\User\User;

class ExperienceGet
{
    private ExperienceRepositoryInterface $repository;
    private ExperienceFileInterface $file;

    public function __construct(ExperienceRepositoryInterface $repository, ExperienceFileInterface $file)
    {
        $this->repository = $repository;
        $this->file = $file;
    }

    public function current(User $user): Experience
    {
        // Load from repository first.
        $experience = $this->repository->load($user);

        // Then try from local file.
        $experience or $experience = $this->file->read($user);

        // Or just instantiate a new one.
        $experience or $experience = new Experience($user);

        return $experience;
    }
}
