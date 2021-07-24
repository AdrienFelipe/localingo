<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Episode;

use App\Localingo\Domain\Episode\Exception\EpisodeVersionException;
use App\Localingo\Domain\Episode\ValueObject\EpisodeState;
use App\Localingo\Domain\Exercise\Exercise;
use App\Localingo\Domain\Exercise\ExerciseCollection;
use App\Localingo\Domain\Exercise\ValueObject\ExerciseType;
use App\Localingo\Domain\Sample\SampleCollection;
use App\Localingo\Domain\User\User;

class Episode
{
    private const VERSION = 5;

    private string $id;
    private User $user;
    private SampleCollection $samples;
    private ExerciseCollection $exercises;
    private ?int $currentExerciseKey;
    private int $version;
    private EpisodeState $state;

    public function __construct(string $id, User $user, SampleCollection $samples)
    {
        $this->version = self::VERSION;
        $this->id = $id;
        $this->user = $user;
        $this->samples = $samples;
        $this->exercises = $this->buildExercises($samples);
        $this->currentExerciseKey = $this->exercises->randomKeyFromAvailable();
        $this->state = EpisodeState::question();
    }

    /**
     * @throws EpisodeVersionException
     */
    public function __wakeup(): void
    {
        if ($this->version !== self::VERSION) {
            throw new EpisodeVersionException();
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getSamples(): SampleCollection
    {
        return $this->samples;
    }

    public function getExercises(): ExerciseCollection
    {
        return $this->exercises;
    }

    public function nextExercise(): ?Exercise
    {
        $key = $this->exercises->randomKeyFromAvailable();
        $this->currentExerciseKey = $key;
        if (!is_int($key)) {
            return null;
        }

        return $this->exercises->offsetGet($key) ?: null;
    }

    private function buildExercises(SampleCollection $samples): ExerciseCollection
    {
        $exercises = new ExerciseCollection();
        foreach ($samples as $sample) {
            foreach (ExerciseType::getAll() as $type) {
                $exercises->append(new Exercise($this, $type, $sample));
            }
        }

        return $exercises;
    }

    public function getCurrentExercise(): ?Exercise
    {
        if (!is_int($this->currentExerciseKey)) {
            return null;
        }

        return $this->exercises->offsetGet($this->currentExerciseKey) ?: null;
    }

    public function setState(EpisodeState $state): void
    {
        $this->state = $state;
    }

    public function getState(): EpisodeState
    {
        return $this->state;
    }
}
