<?php

declare(strict_types=1);

namespace App\Localingo\Application\History;

use App\Localingo\Domain\Exercise\Exercise;
use App\Localingo\Domain\Exercise\ExerciseDTO;
use App\Localingo\Domain\History\HistoryEntry;
use App\Localingo\Domain\History\HistoryFileInterface;

class HistoryRecord
{
    private HistoryFileInterface $file;

    public function __construct(HistoryFileInterface $file)
    {
        $this->file = $file;
    }

    /**
     * @param array<string, bool> $corrections
     */
    public function answered(Exercise $exercise, ExerciseDTO $answers, array $corrections, bool $isCorrect): void
    {
        $expectedDTO = $exercise->getDTO();
        /** @var array<string, string|null> $given */
        $given = [];
        /** @var array<string, string|null> $expected */
        $expected = [];
        foreach ($exercise->getQuestions() as $question) {
            $given[$question] = self::asString($answers->$question);
            $expected[$question] = self::asString($expectedDTO->$question);
        }

        $sample = $exercise->getSample();
        $this->file->append(new HistoryEntry(
            new \DateTimeImmutable(),
            $exercise->getEpisode()->getUser()->getId(),
            $exercise->getEpisode()->getId(),
            (string) $exercise->getType(),
            $isCorrect,
            $given,
            $expected,
            $corrections,
            $sample->getWord(),
            $sample->getDeclined(),
            $sample->getTranslation(),
            $sample->getDeclination(),
            $sample->getNumber(),
            $sample->getGender(),
            $sample->getCase(),
        ));
    }

    private static function asString(mixed $value): ?string
    {
        return is_scalar($value) ? (string) $value : null;
    }
}
