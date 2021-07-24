<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Episode\ValueObject;

class EpisodeState
{
    private const STATE_QUESTION = 'question';
    private const STATE_ANSWER = 'answer';
    private const STATE_NEXT = 'next';
    private const STATE_FINISHED = 'finished';

    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function question(): self
    {
        return new self(self::STATE_QUESTION);
    }

    public function isQuestion(): bool
    {
        return $this->value === self::STATE_QUESTION;
    }

    public static function answer(): self
    {
        return new self(self::STATE_ANSWER);
    }

    public function isAnswer(): bool
    {
        return $this->value === self::STATE_ANSWER;
    }

    public static function next(): self
    {
        return new self(self::STATE_NEXT);
    }

    public function isNext(): bool
    {
        return $this->value === self::STATE_NEXT;
    }

    public static function finished(): self
    {
        return new self(self::STATE_FINISHED);
    }

    public function isFinished(): bool
    {
        return $this->value === self::STATE_FINISHED;
    }
}
