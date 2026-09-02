<?php

declare(strict_types=1);

namespace App\Localingo\Domain\History;

/**
 * One answered exercise, kept to review how exercises get selected over time.
 */
class HistoryEntry
{
    /**
     * @param array<string, string|null> $answers     what was typed, per question field
     * @param array<string, string|null> $expected    what was awaited, per question field
     * @param array<string, bool>        $corrections
     */
    public function __construct(
        public readonly \DateTimeImmutable $at,
        public readonly string $user,
        public readonly string $episode,
        public readonly string $type,
        public readonly bool $correct,
        public readonly array $answers,
        public readonly array $expected,
        public readonly array $corrections,
        public readonly ?string $word,
        public readonly ?string $declined,
        public readonly ?string $translation,
        public readonly ?string $declination,
        public readonly ?string $number,
        public readonly ?string $gender,
        public readonly ?string $case,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function serialize(): array
    {
        return [
            'at' => $this->at->format(\DateTimeInterface::ATOM),
            'user' => $this->user,
            'episode' => $this->episode,
            'type' => $this->type,
            'correct' => $this->correct,
            'answers' => $this->answers,
            'expected' => $this->expected,
            'corrections' => $this->corrections,
            'word' => $this->word,
            'declined' => $this->declined,
            'translation' => $this->translation,
            'declination' => $this->declination,
            'number' => $this->number,
            'gender' => $this->gender,
            'case' => $this->case,
        ];
    }
}
