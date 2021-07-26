<?php

declare(strict_types=1);

namespace App\Localingo\Application\Episode;

use App\Localingo\Application\Exercise\ExerciseCreate;
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
    private const SAMPLES_BY_EPISODE = 15;
    private const EXERCISES_BY_EPISODE = 10;

    private UserGetCurrent $userGetCurrent;
    private UserCreate $userCreate;
    private ExperienceGet $experienceGet;
    private SampleSelect $sampleSelect;
    private ExerciseCreate $exerciseCreate;

    public function __construct(
        UserGetCurrent $userGetCurrent,
        UserCreate $userCreate,
        ExperienceGet $experienceGet,
        SampleSelect $buildSamples,
        ExerciseCreate $exerciseCreate,
    ) {
        $this->userGetCurrent = $userGetCurrent;
        $this->userCreate = $userCreate;
        $this->experienceGet = $experienceGet;
        $this->sampleSelect = $buildSamples;
        $this->exerciseCreate = $exerciseCreate;
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
        $episode = new Episode($id, $user);
        $experience = $this->experienceGet->current($user);

        // Build samples from current experience.
        $samples = $this->sampleSelect->forEpisode($experience,
            self::DECLINATIONS_BY_EPISODE,
            self::WORDS_BY_EPISODE,
            self::SAMPLES_BY_EPISODE
        );
        // Generate exercises from the selected samples.
        $exercises = $this->exerciseCreate->forEpisode($episode, $experience, $samples, self::EXERCISES_BY_EPISODE);
        $episode->setExercises($exercises);

        return $episode;
    }
}
