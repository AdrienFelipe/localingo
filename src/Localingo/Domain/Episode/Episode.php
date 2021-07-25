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
    private const VERSION = 7;

    private int $version;
    private string $id;
    private User $user;
    private ExerciseCollection $exercises;
    private ?string $currentExerciseKey;
    private EpisodeState $state;

    public function __construct(string $id, User $user, SampleCollection $samples)
    {
        $this->version = self::VERSION;
        $this->id = $id;
        $this->user = $user;
        $this->exercises = $this->generateExercises($samples);
        $this->currentExerciseKey = $this->exercises->randomKeyFromAvailable();
        $this->state = EpisodeState::question();
    }

    /**
     * Make sure only up to date entities are unserialized.
     *
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

    public function getExercises(): ExerciseCollection
    {
        return $this->exercises;
    }

    public function nextExercise(): ?Exercise
    {
        $key = $this->exercises->randomKeyFromAvailable();
        $this->currentExerciseKey = $key;
        if (!is_string($key)) {
            return null;
        }

        return $this->exercises->offsetGet($key) ?: null;
    }

    private function generateExercises(SampleCollection $samples): ExerciseCollection
    {
        $exercises = new ExerciseCollection();
        foreach ($samples as $sample) {
            foreach (ExerciseType::getAll() as $type) {
                $exercises->add(new Exercise($this, $type, $sample));
            }
        }

        return $exercises;
    }

    public function getCurrentExercise(): ?Exercise
    {
        if (!is_string($this->currentExerciseKey)) {
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

    /**
     * @param array<string, bool> $filter
     */
    public function applyFilter(array $filter): void
    {
        foreach ($filter as $key => $keep) {
            if (!$keep) {
                $this->exercises->offsetUnset($key);
            }
        }
        // Update current key as it might have been removed.
        $this->currentExerciseKey = $this->exercises->randomKeyFromAvailable();
    }
}
