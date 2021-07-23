<?php

declare(strict_types=1);

namespace App\Localingo\Application\Exercise;

use App\Localingo\Domain\Exercise\Exercise;

class ExerciseBuildForm
{
    public function __construct()
    {
    }

    public function __invoke(Exercise $exercise): mixed
    {
        return null;
    }
}
