<?php

declare(strict_types=1);

namespace App\Localingo\Application\Experience;

use App\Localingo\Domain\Experience\Experience;
use App\Localingo\Domain\User\User;

class ExperienceGet
{
    public function current(User $user): Experience
    {
        return new Experience($user);
    }
}
