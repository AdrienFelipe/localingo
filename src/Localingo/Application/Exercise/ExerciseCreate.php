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
    private const SELECT_TRANSLATION_MAX = 2;
    private const SELECT_WORD_MAX = 5;
    private const SELECT_DECLINED_MIN = 2;
    private const SKIP_NEW_BAD_RATIO_MAX = 2;

    public function forEpisode(Episode $episode, Experience $experience, SampleCollection $samples, int $limit): ExerciseCollection
    {
        // Micro optimization (avoid array count in a foreach).
        $count = 0;
        $exercises = new ExerciseCollection();
        foreach ($samples as $sample) {
            foreach (ExerciseType::getAll() as $type) {
                $exercise = new Exercise($episode, $type, $sample);
                if ($this->selectExercise($exercise, $experience)) {
                    $this->skipNew($exercise, $experience);
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

        $wordItem = $experience->wordItem($exercise->getSample());
        // Translation kind.
        if ($exercise->getType()->isTranslation()) {
            $keep = $wordItem->getGood() <= self::SELECT_TRANSLATION_MAX;
        } // Translation kind.
        elseif ($exercise->getType()->isWord()) {
            $keep = $wordItem->getGood() <= self::SELECT_WORD_MAX;
        }// Declination kind.
        elseif ($exercise->getType()->isDeclined()) {
            $keep = $wordItem->getGood() >= self::SELECT_DECLINED_MIN;
        }

        return $keep;
    }

    private function skipNew(Exercise $exercise, Experience $experience): void
    {
        // Declination is supposed to appear only after a few words try, hence just checking the case experience.
        if ($exercise->getType()->isDeclined()) {
            $experienceItem = $experience->declinationItem($exercise->getSample());
        }
        // Translation and word exercises.
        else {
            $experienceItem = $experience->wordItem($exercise->getSample());
        }

        $hasAGoodAnswer = $experienceItem->getGood();
        $hasFewBadAnswers = $experienceItem->getBadRatio() <= self::SKIP_NEW_BAD_RATIO_MAX;
        $exerciseIsNew = $exercise->getState()->isNew();
        // Skip state 'new' state as soon as item has one good answer, but not too many bad answers.
        if ($hasAGoodAnswer && $hasFewBadAnswers && $exerciseIsNew) {
            $exercise->nextState();
        }
    }
}
