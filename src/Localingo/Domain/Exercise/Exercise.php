<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Exercise;

use App\Localingo\Domain\Exercise\ValueObject\ExerciseState;
use App\Localingo\Domain\Exercise\ValueObject\ExerciseType;
use App\Localingo\Domain\Sample\Sample;

class Exercise
{
    private Sample $sample;
    private ExerciseType $type;
    private ExerciseState $state;
    /** @var string[] */
    private array $questions;

    public function __construct(ExerciseType $type, Sample $sample)
    {
        $this->type = $type;
        $this->sample = $sample;
        $this->state = ExerciseState::open();
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

    public function getType(): ExerciseType
    {
        return $this->type;
    }

    /**
     * @return string[]
     */
    public function getQuestions(): array
    {
        return $this->questions;
    }

    public function getDTO(bool $asExercise = false): ExerciseDTO
    {
        $dto = ExerciseDTO::fromSample($this->sample);
        if ($asExercise) {
            foreach ($this->questions as $question) {
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
