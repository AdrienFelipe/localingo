<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Experience;

use App\Localingo\Domain\Experience\ValueObject\ExperienceItem;
use App\Localingo\Domain\Experience\ValueObject\ExperienceItemCollection;
use App\Localingo\Domain\Sample\Sample;
use App\Localingo\Domain\User\User;

class Experience
{
    private User $user;
    private ExperienceItemCollection $declinationExperiences;
    private ExperienceItemCollection $wordExperiences;
    private ExperienceItemCollection $sampleExperiences;
    private ExperienceItemCollection $caseExperiences;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->declinationExperiences = new ExperienceItemCollection();
        $this->wordExperiences = new ExperienceItemCollection();
        $this->sampleExperiences = new ExperienceItemCollection();
        $this->caseExperiences = new ExperienceItemCollection();
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function addGood(Sample $sample): void
    {
        $this->getDeclinationExperience($sample)->addGood();
        $this->getWordExperience($sample)->addGood();
        $this->getSampleExperience($sample)->addGood();
        $this->getCaseExperience($sample)->addGood();
    }

    public function addBad(Sample $sample, float $score): void
    {
        $this->getDeclinationExperience($sample)->addBad($score);
        $this->getWordExperience($sample)->addBad($score);
        $this->getSampleExperience($sample)->addBad($score);
        $this->getCaseExperience($sample)->addBad($score);
    }

    private function getDeclinationExperience(Sample $sample): ExperienceItem
    {
        return $this->declinationExperiences->getOrAdd($sample->getDeclination());
    }

    private function getWordExperience(Sample $sample): ExperienceItem
    {
        return $this->wordExperiences->getOrAdd($sample->getWord());
    }

    private function getSampleExperience(Sample $sample): ExperienceItem
    {
        $key = "{$sample->getWord()}:{$sample->getDeclination()}:{$sample->getNumber()}";

        return $this->sampleExperiences->getOrAdd($key);
    }

    private function getCaseExperience(Sample $sample): ExperienceItem
    {
        return $this->caseExperiences->getOrAdd($sample->getCase());
    }
}
