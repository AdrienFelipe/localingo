<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Exercise\ValueObject;

class ExerciseType
{
    private const TYPE_TRANSLATION = 'translation';
    private const TYPE_DECLINED = 'declined';

    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function translation(): self
    {
        return new self(self::TYPE_TRANSLATION);
    }

    public function isTranslation(): bool
    {
        return self::TYPE_TRANSLATION === $this->value;
    }

    public static function declined(): self
    {
        return new self(self::TYPE_DECLINED);
    }

    public function isDeclined(): bool
    {
        return self::TYPE_DECLINED === $this->value;
    }
}