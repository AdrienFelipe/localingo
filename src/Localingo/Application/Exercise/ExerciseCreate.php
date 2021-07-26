<?php

declare(strict_types=1);

namespace App\Localingo\Application\Exercise;

use App\Localingo\Domain\Episode\Episode;
use App\Localingo\Domain\Exercise\Exercise;
use App\Localingo\Domain\Exercise\ExerciseCollection;
use App\Localingo\Domain\Exercise\ValueObject\ExerciseType;
use App\Localingo\Domain\Experience\Experience;
use App\Localingo\Domain\Sample\SampleCollection;

class ExerciseCreate
{
    private const SCORE_TRANSLATION_MAX = 4;
    private const SCORE_WORD_MAX = 4;
    private const SCORE_DECLINED_MIN = 2;

    public function forEpisode(Episode $episode, Experience $experience, SampleCollection $samples, int $limit): ExerciseCollection
    {
        // Micro optimization (avoid array count in a foreach).
        $count = 0;
        $exercises = new ExerciseCollection();
        foreach ($samples as $sample) {
            foreach (ExerciseType::getAll() as $type) {
                $exercise = new Exercise($episode, $type, $sample);
                if ($this->selectExercise($exercise, $experience)) {
                    $exercises->add($exercise);

                    // Exit when size limit is reached.
                    if (++$count === $limit) {
                        break 2;
                    }
                }
            }
        }

        return $exercises;
    }

    private function selectExercise(Exercise $exercise, Experience $experience): bool
    {
        // Keep any exercise by default except told otherwise.
        $keep = true;

        $score = $experience->wordItem($exercise->getSample())->getGoodRatio();
        // Translation kind.
        if ($exercise->getType()->isTranslation()) {
            $keep = $score <= self::SCORE_TRANSLATION_MAX;
        } // Translation kind.
        elseif ($exercise->getType()->isWord()) {
            $keep = $score <= self::SCORE_WORD_MAX;
        }// Declination kind.
        elseif ($exercise->getType()->isDeclined()) {
            $keep = $score >= self::SCORE_DECLINED_MIN;
        }

        return $keep;
    }
}
