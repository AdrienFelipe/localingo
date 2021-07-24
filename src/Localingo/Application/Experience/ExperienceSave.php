<?php

declare(strict_types=1);

namespace App\Localingo\Application\Experience;

use App\Localingo\Domain\Experience\Experience;
use App\Localingo\Domain\Experience\ExperienceFileInterface;
use App\Localingo\Domain\Experience\ExperienceRepositoryInterface;

class ExperienceSave
{
    private ExperienceRepositoryInterface $repository;
    private ExperienceFileInterface $file;

    /**
     * ExperienceSave constructor.
     */
    public function __construct(ExperienceRepositoryInterface $repository, ExperienceFileInterface $file)
    {
        $this->repository = $repository;
        $this->file = $file;
    }

    public function apply(Experience $experience): void
    {
        $this->repository->save($experience);
        $this->file->write($experience);
    }
}
