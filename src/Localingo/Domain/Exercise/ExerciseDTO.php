<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Exercise;

use App\Localingo\Domain\Sample\Sample;

class ExerciseDTO
{
    public ?string $declined;
    public ?string $declination;
    public ?string $number;
    public ?string $gender;
    public ?string $word;
    public ?string $translation;
    public ?string $state;
    public ?string $case;

    public function __construct(?string $declined, ?string $declination, ?string $number, ?string $gender, ?string $word, ?string $translation, ?string $state, ?string $case)
    {
        $this->declined = $declined;
        $this->declination = $declination;
        $this->number = $number;
        $this->gender = $gender;
        $this->word = $word;
        $this->translation = $translation;
        $this->state = $state;
        $this->case = $case;
    }

    public static function fromSample(Sample $sample): self
    {
        return new self(
            $sample->getDeclined(),
            $sample->getDeclination(),
            $sample->getNumber(),
            $sample->getGender(),
            $sample->getWord(),
            $sample->getTranslation(),
            $sample->getState(),
            $sample->getCase(),
        );
    }
}
