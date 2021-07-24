<?php

declare(strict_types=1);

namespace App\Localingo\Application\Exercise;

use App\Localingo\Application\Experience\ExperienceGet;
use App\Localingo\Application\Experience\ExperienceSave;
use App\Localingo\Domain\Exercise\Exception\ExerciseMissingStateOrder;
use App\Localingo\Domain\Exercise\Exercise;

class ExerciseExecute
{
    private ExperienceGet $experienceGet;
    private ExperienceSave $experienceSave;

    public function __construct(ExperienceGet $experienceGet, ExperienceSave $experienceSave)
    {
        $this->experienceGet = $experienceGet;
        $this->experienceSave = $experienceSave;
    }

    /**
     * @throws ExerciseMissingStateOrder
     */
    public function applyAnswer(Exercise $exercise, bool $isCorrect): void
    {
        // Update exercise.
        $isCorrect ? $exercise->nextState() : $exercise->previousState();

        // Update experience.
        $user = $exercise->getEpisode()->getUser();
        $experience = $this->experienceGet->current($user);
        $sample = $exercise->getSample();
        $isCorrect ? $experience->addGood($sample) : $experience->addBad($sample);
        $this->experienceSave->apply($experience);
    }
}
