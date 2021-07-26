<?php

declare(strict_types=1);

namespace App\Localingo\Application\Exercise;

use App\Localingo\Application\Experience\ExperienceExecute;
use App\Localingo\Domain\Episode\Episode;
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
        // Update experience (before the exercise).
        $this->experienceExecute->applyAnswer($exercise, $isCorrect);

        // Update exercise.
        $isCorrect ? $exercise->nextState() : $exercise->previousState();
        // Remove the state 'new' from all other same word exercises.
        $this->updateExercises($exercise->getEpisode(), $exercise->getSample()->getWord());
    }

    /**
     * @throws ExerciseMissingStateOrder
     */
    private function updateExercises(Episode $episode, string $word): void
    {
        // Remove the state 'new' from all other same word exercises.
        foreach ($episode->getExercises() as $exercise) {
            $hasSameWord = $exercise->getSample()->getWord() === $word;
            $isNew = $exercise->getState()->isNew();
            $isNotDeclined = !$exercise->getType()->isDeclined();
            if ($hasSameWord && $isNew && $isNotDeclined) {
                $exercise->nextState();
            }
        }
    }
}
