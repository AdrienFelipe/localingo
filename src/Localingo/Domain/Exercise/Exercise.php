<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Exercise;

use App\Localingo\Domain\Episode\Episode;
use App\Localingo\Domain\Exercise\ValueObject\ExerciseState;
use App\Localingo\Domain\Exercise\ValueObject\ExerciseType;
use App\Localingo\Domain\Sample\Sample;

class Exercise
{
    private Episode $episode;
    private ExerciseType $type;
    private Sample $sample;
    private ExerciseState $state;
    /** @var string[] */
    private array $questions;

    public function __construct(Episode $episode, ExerciseType $type, Sample $sample)
    {
        $this->episode = $episode;
        $this->type = $type;
        $this->sample = $sample;
        $this->state = ExerciseState::new();
        $this->questions = $this->buildQuestions($type);
    }

    /**
     * @return string[]
     */
    private function buildQuestions(ExerciseType $type): array
    {
        $questions = [];
        if ($type->isTranslation()) {
            $questions[] = (string) $this->getDTO()->asPropertyNames()->translation;
        } elseif ($type->isDeclined()) {
            $questions[] = (string) $this->getDTO()->asPropertyNames()->declined;
        }

        return $questions;
    }

    public function getSample(): Sample
    {
        return $this->sample;
    }

    public function getEpisode(): Episode
    {
        return $this->episode;
    }

    public function getType(): ExerciseType
    {
        return $this->type;
    }

    /**
     * @return string[]
     */
    public function getQuestions(): array
    {
        return $this->state->isNew() ? [] : $this->questions;
    }

    /**
     * Build a key that is not unique for similar cases to be merged.
     */
    public function getKey(): string
    {
        // Simplify readability.
        $type = $this->type;
        $sample = $this->sample;
        $key = "$type:{$sample->getWord()}";

        // Merge similar cases to avoid repetition.
        if ($type->isTranslation() || $type->isWord()) {
            return $key;
        }

        return "$key:{$sample->getDeclination()}:{$sample->getNumber()}";
    }

    public function getDTO(bool $asExercise = false): ExerciseDTO
    {
        $dto = ExerciseDTO::fromSample($this->sample);
        if ($asExercise) {
            foreach ($this->getQuestions() as $question) {
                $dto->$question = null;
            }
        }

        return $dto;
    }

    public function getState(): ExerciseState
    {
        return $this->state;
    }

    /**
     * @throws Exception\ExerciseMissingStateOrder
     */
    public function previousState(): void
    {
        $this->state = $this->state->previous();
    }

    /**
     * @throws Exception\ExerciseMissingStateOrder
     */
    public function nextState(): void
    {
        $this->state = $this->state->next();
    }
}
