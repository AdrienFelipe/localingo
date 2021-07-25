<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Exercise\ValueObject;

class ExerciseType
{
    private const TYPE_TRANSLATION = 'translation';
    private const TYPE_DECLINED = 'declined';
    private const TYPE_WORD = 'word';

    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * @return self[]
     */
    public static function getAll(): array
    {
        return [
            self::translation(),
            self::word(),
            self::declined(),
        ];
    }

    public static function translation(): self
    {
        return new self(self::TYPE_TRANSLATION);
    }

    public function isTranslation(): bool
    {
        return $this->value === self::TYPE_TRANSLATION;
    }

    public static function word(): self
    {
        return new self(self::TYPE_WORD);
    }

    public function isWord(): bool
    {
        return $this->value === self::TYPE_WORD;
    }

    public static function declined(): self
    {
        return new self(self::TYPE_DECLINED);
    }

    public function isDeclined(): bool
    {
        return $this->value === self::TYPE_DECLINED;
    }
}
