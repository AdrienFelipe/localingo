<?php

declare(strict_types=1);

namespace App\Localingo\Application\Exercise;

use App\Localingo\Domain\Episode\Episode;
use App\Localingo\Domain\Experience\Experience;

class ExerciseSelect
{
    private const SCORE_TRANSLATION_MAX = 4;
    private const SCORE_WORD_MAX = 4;
    private const SCORE_DECLINED_MIN = 2;

    /**
     * @return array<string, bool>
     */
    public function experienceFilter(Episode $episode, Experience $experience): array
    {
        $filter = [];
        foreach ($episode->getExercises() as $key => $exercise) {
            $keep = false;
            $score = $experience->wordItem($exercise->getSample())->getGoodRatio();
            if ($exercise->getType()->isTranslation()) {
                $keep = $score <= self::SCORE_TRANSLATION_MAX;
            } elseif ($exercise->getType()->isWord()) {
                $keep = $score <= self::SCORE_WORD_MAX;
            } elseif ($exercise->getType()->isDeclined()) {
                $keep = $score >= self::SCORE_DECLINED_MIN;
            }
            $filter[$key] = $keep;
        }

        return $filter;
    }

    /**
     * @return array<string, bool>
     */
    public function maxFilter(Episode $episode, int $max): array
    {
        $count = 0;
        $filter = [];
        foreach (array_keys($episode->getExercises()->getArrayCopy()) as $key) {
            $filter[$key] = ++$count <= $max;
        }

        return $filter;
    }
}
