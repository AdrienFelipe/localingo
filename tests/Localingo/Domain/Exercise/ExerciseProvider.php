<?php

namespace App\Tests\Localingo\Domain\Exercise;

use App\Localingo\Domain\Exercise\Exercise;
use App\Localingo\Domain\Exercise\ValueObject\ExerciseType;
use App\Tests\Localingo\Domain\Episode\EpisodeProvider;
use App\Tests\Localingo\Domain\Sample\SampleProvider;

class ExerciseProvider
{
    public static function word(bool $isNew = true): Exercise
    {
        return self::build(ExerciseType::word(), $isNew);
    }

    public static function translation(bool $isNew = true): Exercise
    {
        return self::build(ExerciseType::translation(), $isNew);
    }

    public static function declined(bool $isNew = true): Exercise
    {
        return self::build(ExerciseType::declined(), $isNew);
    }

    private static function build(ExerciseType $type, bool $isNew): Exercise
    {
        $episode = EpisodeProvider::default();
        $sample = SampleProvider::default();
        $exercise = new Exercise($episode, $type, $sample);
        if (!$isNew) {
            $exercise->nextState();
        }

        return $exercise;
    }
}
