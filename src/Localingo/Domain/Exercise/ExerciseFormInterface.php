<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Exercise;

interface ExerciseFormInterface
{
    public function isSubmitted(Exercise $exercise): bool;

    public function getSubmitted(Exercise $exercise): ExerciseDTO;

    public function buildExerciseForm(Exercise $exercise): mixed;

    /**
     * @param array<string, bool> $corrections
     */
    public function buildAnswersForm(Exercise $exercise, array $corrections): mixed;
}
