<?php

declare(strict_types=1);

namespace App\Localingo\Application\Experience;

use App\Localingo\Domain\Episode\Episode;
use App\Localingo\Domain\Exercise\Exercise;
use App\Localingo\Domain\Experience\Experience;
use App\Localingo\Domain\User\User;

class ExperienceExecute
{
    private ExperienceGet $experienceGet;
    private ExperienceSave $experienceSave;

    public function __construct(ExperienceGet $experienceGet, ExperienceSave $experienceSave)
    {
        $this->experienceGet = $experienceGet;
        $this->experienceSave = $experienceSave;
    }

    public function applyAnswer(Exercise $exercise, bool $isCorrect): void
    {
        // Makes no sense to add experience from the initial visualization.
        if (!$exercise->getState()->isNew()) {
            $experience = $this->getExperience($exercise->getEpisode()->getUser());
            $isCorrect ? $experience->addGood($exercise) : $experience->addBad($exercise);
            $this->experienceSave->toRepository($experience);
        }
    }

    public function applyFinished(Episode $episode): void
    {
        $experience = $this->getExperience($episode->getUser());
        $this->experienceSave->toFile($experience);
    }

    private function getExperience(User $user): Experience
    {
        return $this->experienceGet->current($user);
    }
}
