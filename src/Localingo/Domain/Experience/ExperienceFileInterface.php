<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Experience;

use App\Localingo\Domain\User\User;

interface ExperienceFileInterface
{
    public function read(User $user): ?Experience;

    public function write(Experience $experience): void;
}
