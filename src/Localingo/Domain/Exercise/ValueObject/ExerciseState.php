<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Exercise\ValueObject;

use App\Localingo\Domain\Exercise\Exception\ExerciseMissingStateOrder;

class ExerciseState
{
    private const STATE_FAILED = 'failed';
    private const STATE_OPEN = 'open';
    private const STATE_DONE = 'done';

    private const STATES_ORDER = [
        self::STATE_FAILED,
        self::STATE_OPEN,
        self::STATE_DONE,
    ];

    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function failed(): self
    {
        return new self(self::STATE_FAILED);
    }

    public function is_failed(): bool
    {
        return $this->value === self::STATE_FAILED;
    }

    public static function open(): self
    {
        return new self(self::STATE_OPEN);
    }

    public function is_open(): bool
    {
        return $this->value === self::STATE_OPEN;
    }

    public static function done(): self
    {
        return new self(self::STATE_DONE);
    }

    public function is_done(): bool
    {
        return $this->value === self::STATE_DONE;
    }

    /**
     * @throws ExerciseMissingStateOrder
     */
    public function previous(): self
    {
        $key = array_search($this->value, self::STATES_ORDER);
        if (!is_int($key)) {
            throw new ExerciseMissingStateOrder();
        }

        $value = self::STATES_ORDER[$key - 1] ?? $this->value;

        return new self($value);
    }

    /**
     * @throws ExerciseMissingStateOrder
     */
    public function next(): self
    {
        $key = array_search($this->value, self::STATES_ORDER);
        if (!is_int($key)) {
            throw new ExerciseMissingStateOrder();
        }

        $value = self::STATES_ORDER[$key + 1] ?? $this->value;

        return new self($value);
    }
}
