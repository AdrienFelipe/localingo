<?php

declare(strict_types=1);

namespace App\Localingo\Application\Experience;

use App\Localingo\Domain\Experience\Experience;
use App\Localingo\Domain\Experience\ExperienceRepositoryInterface;
use App\Localingo\Domain\User\User;

class ExperienceGet
{
    private ExperienceRepositoryInterface $repository;

    public function __construct(ExperienceRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function current(User $user): Experience
    {
        if (!$experience = $this->repository->load($user)) {
            $experience = new Experience($user);
        }

        return $experience;
    }
}
