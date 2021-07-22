<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Exercise;

use App\Localingo\Domain\Exercise\ValueObject\ExerciseType;
use App\Localingo\Domain\Sample\Sample;

class Exercise
{
    private Sample $sample;
    private ExerciseType $type;
    private ExerciseDTO $DTO;

    public function __construct(ExerciseType $type, Sample $sample)
    {
        $this->type = $type;
        $this->sample = $sample;
        $this->DTO = $this->setupDTO($type, $sample);
    }

    private function setupDTO(ExerciseType $type, Sample $sample): ExerciseDTO
    {
        $dto = ExerciseDTO::fromSample($sample);
        if ($type->isTranslation()) {
            $dto->translation = null;
        } elseif ($type->isDeclined()) {
            $dto->declined = null;
        }

        return $dto;
    }

    public function getSample(): Sample
    {
        return $this->sample;
    }

    public function getType(): ExerciseType
    {
        return $this->type;
    }

    public function getDTO(): ExerciseDTO
    {
        return clone $this->DTO;
    }
}
