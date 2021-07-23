<?php

declare(strict_types=1);

namespace App\Localingo\Application\Exercise;

use App\Localingo\Domain\Exercise\Exercise;
use App\Localingo\Domain\Exercise\ExerciseDTO;

class ExerciseValidation
{
    /**
     * @return array<string, bool>
     */
    public function getCorrections(Exercise $exercise, ExerciseDTO $answers): array
    {
        $corrections = [];
        foreach ($exercise->getQuestions() as $question) {
            $rightAnswer = strtolower((string) $exercise->getDTO()->$question);
            $userAnswer = strtolower(trim((string) $answers->$question));
            $corrections[$question] = $userAnswer === $rightAnswer;
        }

        return $corrections;
    }
}
