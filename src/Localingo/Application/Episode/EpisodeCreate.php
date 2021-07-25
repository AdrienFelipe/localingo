<?php

declare(strict_types=1);

namespace App\Localingo\Application\Episode;

use App\Localingo\Application\Experience\ExperienceGet;
use App\Localingo\Application\Sample\SampleBuildCollection;
use App\Localingo\Application\User\UserCreate;
use App\Localingo\Application\User\UserGetCurrent;
use App\Localingo\Domain\Episode\Episode;
use Exception;

class EpisodeCreate
{
    private const WORDS_BY_EPISODE = 1;
    private UserGetCurrent $userGetCurrent;
    private UserCreate $userCreate;
    private ExperienceGet $experienceGet;
    private SampleBuildCollection $buildSamples;

    public function __construct(
        UserGetCurrent $userGetCurrent,
        UserCreate $userCreate,
        ExperienceGet $experienceGet,
        SampleBuildCollection $buildSamples,
    ) {
        $this->userGetCurrent = $userGetCurrent;
        $this->userCreate = $userCreate;
        $this->experienceGet = $experienceGet;
        $this->buildSamples = $buildSamples;
    }

    public function new(): Episode
    {
        // TODO: Check against id collisions (search for existing ids in a while loop).
        try {
            $id = (string) random_int(1, 10000);
        } catch (Exception) {
            $id = '0';
        }

        // Load or create user.
        $user = ($this->userGetCurrent)() ?: ($this->userCreate)();
        $experience = $this->experienceGet->current($user);
        // Choose word selection.
        $samples = $this->buildSamples->build($experience, self::WORDS_BY_EPISODE);

        return new Episode($id, $user, $samples);
    }
}
