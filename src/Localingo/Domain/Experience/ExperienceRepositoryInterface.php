<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Experience;

use App\Localingo\Domain\User\User;

interface ExperienceRepositoryInterface
{
    public function load(User $user): ?Experience;

    public function save(Experience $experience): void;
}
