<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Exercise;

use App\Localingo\Domain\Sample\Sample;

interface ExerciseFormInterface
{
    public function build(Sample $sample): mixed;
}
