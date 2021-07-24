<?php

declare(strict_types=1);

namespace App\Localingo\Application\Exercise;

use App\Localingo\Application\Experience\ExperienceExecute;
use App\Localingo\Domain\Exercise\Exception\ExerciseMissingStateOrder;
use App\Localingo\Domain\Exercise\Exercise;

class ExerciseExecute
{
    private ExperienceExecute $experienceExecute;

    public function __construct(ExperienceExecute $experienceExecute)
    {
        $this->experienceExecute = $experienceExecute;
    }

    /**
     * @throws ExerciseMissingStateOrder
     */
    public function applyAnswer(Exercise $exercise, bool $isCorrect): void
    {
        // Update exercise.
        $isCorrect ? $exercise->nextState() : $exercise->previousState();

        // Update experience.
        $this->experienceExecute->applyAnswer($exercise, $isCorrect);
    }
}
