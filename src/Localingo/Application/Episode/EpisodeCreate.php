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
    private const MAX_WORDS = 20;
    private const MAX_SAMPLES = 15;
    private const MAX_EXERCISES = 10;

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
        $samples = $this->sampleSelect->forEpisode(
            $experience,
            self::MAX_WORDS,
            self::MAX_SAMPLES
        );
        // Generate exercises from selected samples.
        $exercises = $this->exerciseCreate->forEpisode($episode, $experience, $samples, self::MAX_EXERCISES);
        $episode->setExercises($exercises);

        return $episode;
    }
}
