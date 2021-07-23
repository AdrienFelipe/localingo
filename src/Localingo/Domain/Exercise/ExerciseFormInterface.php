<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Exercise;

interface ExerciseFormInterface
{
    public function initialize(Exercise $exercise): void;

    public function isSubmitted(): bool;

    public function getSubmitted(): ExerciseDTO;

    public function buildExerciseForm(Exercise $exercise): mixed;

    /**
     * @param array<string, bool> $corrections
     */
    public function buildAnswersForm(Exercise $exercise, array $corrections): mixed;
}
