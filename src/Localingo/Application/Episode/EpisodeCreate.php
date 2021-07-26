<?php

declare(strict_types=1);

namespace App\Localingo\Application\Episode;

use App\Localingo\Application\Exercise\ExerciseCreate;
use App\Localingo\Application\Experience\ExperienceGet;
use App\Localingo\Application\Sample\SampleSelect;
use App\Localingo\Application\User\UserCreate;
use App\Localingo\Application\User\UserGet;
use App\Localingo\Domain\Episode\Episode;

class EpisodeCreate
{
    private const DECLINATIONS_PER_EPISODE = 1;
    private const WORDS_PER_EPISODE = 4;
    private const SAMPLES_PER_EPISODE = 15;
    private const EXERCISES_PER_EPISODE = 10;

    private UserGet $userGet;
    private UserCreate $userCreate;
    private ExperienceGet $experienceGet;
    private SampleSelect $sampleSelect;
    private ExerciseCreate $exerciseCreate;

    public function __construct(
        UserGet $userGet,
        UserCreate $userCreate,
        ExperienceGet $experienceGet,
        SampleSelect $buildSamples,
        ExerciseCreate $exerciseCreate,
    ) {
        $this->userGet = $userGet;
        $this->userCreate = $userCreate;
        $this->experienceGet = $experienceGet;
        $this->sampleSelect = $buildSamples;
        $this->exerciseCreate = $exerciseCreate;
    }

    public function new(): Episode
    {
        // Load or create user.
        $user = $this->userGet->current() ?: $this->userCreate->new();
        $experience = $this->experienceGet->current($user);

        // TODO: Check against id collisions.
        $id = (string) random_int(1, 10000);
        $episode = new Episode($id, $user);

        // Build samples from current experience.
        $samples = $this->sampleSelect->forEpisode($experience,
            self::DECLINATIONS_PER_EPISODE,
            self::WORDS_PER_EPISODE,
            self::SAMPLES_PER_EPISODE
        );
        // Generate exercises from selected samples.
        $exercises = $this->exerciseCreate->forEpisode($episode, $experience, $samples, self::EXERCISES_PER_EPISODE);
        $episode->setExercises($exercises);

        return $episode;
    }
}
