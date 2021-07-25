<?php

declare(strict_types=1);

namespace App\Localingo\Application\Episode;

use App\Localingo\Application\Exercise\ExerciseSelect;
use App\Localingo\Application\Experience\ExperienceGet;
use App\Localingo\Application\Sample\SampleSelect;
use App\Localingo\Application\User\UserCreate;
use App\Localingo\Application\User\UserGetCurrent;
use App\Localingo\Domain\Episode\Episode;
use Exception;

class EpisodeCreate
{
    private const DECLINATIONS_BY_EPISODE = 1;
    private const WORDS_BY_EPISODE = 4;
    private const SAMPLES_BY_EPISODE = 10;
    private const EXERCISES_BY_EPISODE = 10;

    private UserGetCurrent $userGetCurrent;
    private UserCreate $userCreate;
    private ExperienceGet $experienceGet;
    private SampleSelect $sampleSelect;
    private ExerciseSelect $exerciseSelect;

    public function __construct(
        UserGetCurrent $userGetCurrent,
        UserCreate $userCreate,
        ExperienceGet $experienceGet,
        SampleSelect $buildSamples,
        ExerciseSelect $exerciseSelect
    ) {
        $this->userGetCurrent = $userGetCurrent;
        $this->userCreate = $userCreate;
        $this->experienceGet = $experienceGet;
        $this->sampleSelect = $buildSamples;
        $this->exerciseSelect = $exerciseSelect;
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
        $samples = $this->sampleSelect->forEpisode(
            $experience,
            self::DECLINATIONS_BY_EPISODE,
            self::WORDS_BY_EPISODE,
            self::SAMPLES_BY_EPISODE
        );

        $episode = new Episode($id, $user, $samples);
        // Select exercises based on current experience.
        $filter = $this->exerciseSelect->experienceFilter($episode, $experience);
        $episode->applyFilter($filter);
        // Keep only the desired amount of exercises.
        $filter = $this->exerciseSelect->maxFilter($episode, self::EXERCISES_BY_EPISODE);
        $episode->applyFilter($filter);

        return $episode;
    }
}
