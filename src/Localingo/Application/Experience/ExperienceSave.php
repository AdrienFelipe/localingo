<?php

declare(strict_types=1);

namespace App\Localingo\Application\Experience;

use App\Localingo\Domain\Experience\Experience;
use App\Localingo\Domain\Experience\ExperienceRepositoryInterface;

class ExperienceSave
{
    private ExperienceRepositoryInterface $repository;

    /**
     * ExperienceSave constructor.
     */
    public function __construct(ExperienceRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function apply(Experience $experience): void
    {
        $this->repository->save($experience);
    }
}
