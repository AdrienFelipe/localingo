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
        $experience = $this->getExperience($exercise->getEpisode()->getUser());
        $sample = $exercise->getSample();
        $isCorrect ? $experience->addGood($sample) : $experience->addBad($sample);
        $this->experienceSave->toRepository($experience);
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
